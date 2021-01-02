<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrendController extends Controller
{
    // トレンド表示処理
    public function index()
    {
        // トレンドテーブルから全データを取得し、ツイート数の多い順にレコードを並べ替える
        $trends = Trend::orderBy('high', 'DESC')->get();
        // $trends = Trend::all();

        foreach ($trends as $trend) {
            // 通貨ペアがある通貨は最高価格、最安価格の数値をカンマ区切りにフォーマット
            if ($trend->currency_pair) {
                $trend->high = number_format($trend->high);
                $trend->low = number_format($trend->low);
            } else {
                // 通貨ペアが登録されていない通貨は'不明'を表示
                $trend->high = '不明';
                $trend->low = '不明';
            }
        }
        // バッチの最終実行日時をバッチテーブルから取得
        $batch =  Batch::select('batch_finished_at')->where('batch_name', 'update_prices')->first();
        // 日時形式を変換（Batchモデルの$datesプロパティに'batch_finished_at'カラムを指定する）
        $updated_at = $batch->batch_finished_at->format('Y/m/d H:i');
        // 各通貨の情報と更新日時を配列に格納
        $items['trends'] = $trends;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }

    // 過去24時間の最高取引価格、最安取引価格をDBに保存
    public function getPrices()
    {
        // Trendsテーブルから全レコードを取得
        $currency_records = Trend::all();
        // $currency_records = Trend::select(['currency_name', 'currency_pair', 'used_api_type', 'high', 'low', 'updated_at'])->get();

        // それぞれの仮想通貨についてループ処理を行う
        foreach ($currency_records as $currency_record) {
            $api = $currency_record->used_api_type; //使用するAPI（使用しない場合は0）
            $pair = $currency_record->currency_pair; //通貨ペアの種類

            // その通貨の通貨ペア情報がDBに登録されている場合は、DBで指定した種類のAPIから最新価格を取得する
            if ($pair) {
                if ($api === 1) {
                    // CoinCheckAPIから価格情報を取得
                    $newest_price = $this->getPricesFromCoinCheck($pair);
                } elseif ($api === 2) {
                    // ZaifAPIから価格情報を取得
                    $newest_price = $this->getPricesFromZaif($pair);
                } elseif ($api === 3) {
                    // bitbankAPIから価格情報を取得
                    $newest_price = $this->getPricesFromBitBank($pair);
                }
            } else {
                // 通貨ペア情報がDBに未登録の場合は空文字を格納する
                $newest_price = '';
            }

            $table_updated = false; //DB更新の有無を判定するフラグ

            // APIから最高取引価格を取得しており、かつDBの最高取引価格と異なる場合は、その通貨の最高取引価格を上書きする
            if ($newest_price && $currency_record->high <> $newest_price['high']) {
                $currency_record->high = $newest_price['high'];
                logger()->info('high:' . $currency_record->high . '-' . $currency_record->currency_name); // ヘルパー関数info()でログを出力
                $table_updated = true; //更新フラグをtrueに変更
            }
            // APIから最安取引価格を取得しており、かつDBの最安取引価格と異なる場合は、その通貨の最低取引価格を上書きする
            if ($newest_price && $currency_record->low <> $newest_price['low']) {
                $currency_record->low = $newest_price['low'];
                logger()->info('low:' . $currency_record->low . '-' . $currency_record->currency_name); // ヘルパー関数info()でログを出力
                $table_updated = true; //更新フラグをtrueに変更
            }
            // 更新フラグがtrueの場合は更新日時に現在日時を代入し、価格と共にDBの値を更新する
            if ($table_updated) {
                $currency_record->updated_at = Carbon::now();
                $currency_record->save();
                logger()->info($currency_record->currency_name . 'の価格を更新' . $currency_record->updated_at); // ヘルパー関数info()でログを出力
            }
        }
        return;
    }


    // ZaifAPIで過去24時間の最高取引価格、最安取引価格を取得
    public function getPricesFromZaif(string $currency_pair)
    {
        // ZaifAPI エンドポイントURL
        $endpoint = 'https://api.zaif.jp/api/1';

        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy', 'eth_jpy', 'xem_jpy', 'bch_jpy', 'mona_jpy'];

        // 引数で渡された通貨ペアが取得可能な通貨ペアに含まれる場合は、APIから通貨ペアのティッカーを取得
        // API Doc: https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/4_ticker.html?highlight=ticker
        if (in_array($currency_pair, $active_pairs)) {
            // API URL: /ticker/{currency_pair}
            $url = $endpoint . '/ticker/' . $currency_pair;
            $prices_json = file_get_contents($url);
            $prices_obj = json_decode($prices_json);
            $lists = [
                'high' => $prices_obj->high, // 過去24時間の高値
                'low' => $prices_obj->low, // 過去24時間の安値
            ];

            // $prices_obj キー一覧
            // キー	詳細	型
            // last	終値	float
            // high	過去24時間の高値	float
            // low	過去24時間の安値	float
            // vwap	過去24時間の加重平均	float
            // volume	過去24時間の出来高	float
            // bid	買気配値	float
            // ask	売気配値	float
        } else {
            $lists = '';
        }
        return $lists;
    }

    //  bitbankAPIで過去24時間の最高取引価格、最安取引価格を取得
    public function getPricesFromBitBank(string $currency_pair)
    {
        // bitbankAPI エンドポイントURL
        $endpoint = 'https://public.bitbank.cc';

        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy', 'eth_jpy', 'xrp_jpy', 'ltc_jpy', 'mona_jpy', 'xlm_jpy'];

        // 引数で渡された通貨ペアが取得可能な通貨ペアに含まれる場合は、APIから通貨ペアのティッカーを取得
        // API Doc: https://github.com/bitbankinc/bitbank-api-docs/blob/master/public-api_JP.md#ticker
        if (in_array($currency_pair, $active_pairs)) {
            // API URL: /{pair}/ticker
            $url = $endpoint . '/' . $currency_pair . '/ticker';
            $prices_json = file_get_contents($url);
            $prices_obj = json_decode($prices_json);
            // dd($prices_obj);

            $lists = [
                'high' => $prices_obj->data->high, // 過去24時間の高値
                'low' => $prices_obj->data->low, // 過去24時間の安値
            ];

            // $prices_obj キー一覧
            // Name	Type	Description
            // sell	string	現在の売り注文の最安値
            // buy	string	現在の買い注文の最高値
            // high	string	過去24時間の最高値取引価格
            // low	string	過去24時間の最安値取引価格
            // last	string	最新取引価格
            // vol	string	過去24時間の出来高
            // timestamp	number	日時（UnixTimeのミリ秒）
        } else {
            $lists = '';
        }
        return $lists;
    }

    // coincheckで過去24時間の最高取引価格、最安取引価格を取得
    public function getPricesFromCoinCheck(string $currency_pair)
    {
        // coincheck API エンドポイントURL
        $endpoint = 'https://coincheck.com/api/ticker';
        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy'];

        // 引数で渡された通貨ペアが取得可能な通貨ペアに含まれる場合は、APIから通貨ペアのティッカーを取得
        // API Doc: https://coincheck.com/ja/documents/exchange/api#ticker
        if (in_array($currency_pair, $active_pairs)) {
            $url = $endpoint;
            $json_str = file_get_contents($url);
            $prices = json_decode($json_str);
            $lists = [
                'high' => $prices->high, // 過去24時間の高値
                'low' => $prices->low, // 過去24時間の安値
            ];
        } else {
            $lists = '';
        }
        return $lists;
    }


    // キーワードを含むツイートを取得して保存する処理
    public function getTweet()
    {
        $bearer_token = 'AAAAAAAAAAAAAAAAAAAAAGE1LAEAAAAAHsZcrDgnnO3Je0XRBXNlZMbcRKQ%3DHtfSfMmaE6d4Z21HqJYv6G6yzfsqLtIIunLf3h4S5BnHpgF1MN';
        $request_url = 'https://api.twitter.com/1.1/search/tweets.json';
        // $request_url = 'https://api.twitter.com/2/tweets/search/recent';

        $keywords = 'ETH BTC';

        // パラメータ (オプション)
        $options = array(
            'q' => $keywords,
            'count' => '100',
            'lang' => 'ja',
            'locale' => 'ja',
            'result_type' => 'recent',
            // "until" => "2017-01-17",
            // "max_id" => "643299864344788992",
            // "include_entities" => "true",
        );

        // $since_id = getMaxID(); //DBから現在の最大TweetIDを取得する処理
        // if ($since_id) {
        //     $options['since_id'] = $since_id; //前回の最後に取得したツイートIDから
        // }

        // パラメータがある場合
        if ($options) {
            $request_url .= '?' . http_build_query($options);
        }


        // リクエスト用のコンテキスト
        $context = array(
            'http' => array(
                'method' => 'GET', // リクエストメソッド
                'header' => array(              // ヘッダー
                    'Authorization: Bearer ' . $bearer_token,
                ),
            ),
        );

        // cURLを使ってリクエスト
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']); // メソッド
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // curl_execの結果を文字列で返す
        curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http']['header']); // ヘッダー
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); // タイムアウトの秒数
        $res1 = curl_exec($curl);
        $res2 = curl_getinfo($curl);
        curl_close($curl);

        // 取得したデータ
        $json_data = substr($res1, $res2['header_size']); // 取得したデータ(JSONなど)
        // if ($json_data) {
        //     $statuses = $json_data['statuses']; //ステータス情報取得
        // }
        dd(json_decode($json_data));

        // if ($statuses && is_array($statuses)) {
        //     $sts_cnt = count($statuses);
        //     // 一番古いデータからDBへ書き込む
        //     for ($i = $sts_cnt - 1; $i >= 0; $i--) {
        //         $result = $statuses[$i];
        //         $has_media = true;
        //         $screen_name = $result['user']['screen_name'];
        //         $twitter_id = $result['user']['id_str'];

        //         //$cnt++;
        //         $tw_created_date = date('Y-m-d H:i:s', strtotime($result["created_at"]));
        //         $user_name      = $result['user']['name'];
        //         $tweet_id       = $result['id_str'];
        //         $profile_url    = $result['user']['profile_image_url'];
        //         $tweet_text     = $result['text'];

        //         $img_src = $short_url = $display_url = "";

        //         if (isset($result["entities"]['media'])) { //写真等がある場合、取得（ビデオリンクの場合、違う方法で取得可能）
        //             // 最初のメディアのみを取得する（全部取得できるように修正を)
        //             $img_src        = $result["entities"]['media'][0]['media_url'];
        //             $short_url      = $result["entities"]['media'][0]['url'];
        //             $display_url    = $result["entities"]['media'][0]['display_url'];
        //         }

        //         // DBへデータを書き込む処理
        //         writeToDatabase();
        //     }
        // }
    }
}
