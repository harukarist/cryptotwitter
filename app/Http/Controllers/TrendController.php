<?php

namespace App\Http\Controllers;

use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrendController extends Controller
{
    // トレンド表示処理
    public function index()
    {
        $trends = Trend::orderBy('high', 'DESC')->get();
        // $trends = Trend::all();

        foreach ($trends as $trend) {
            if ($trend->currency_pair) {
                $trend->high = number_format($trend->high);
                $trend->low = number_format($trend->low);
            } else {
                $trend->high = '不明';
                $trend->low = '不明';
            }
        }

        $updated_at = $trends->first()->updated_at->format('Y/m/d H:i');

        $items['trends'] = $trends;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }

    // 過去24時間の最高取引価格、最安取引価格をDBに保存
    public function getTicker()
    {
        $trends = Trend::all();

        foreach ($trends as $trend) {
            $api = $trend->used_api_type;
            $pair = $trend->currency_pair;
            if ($pair) {
                if ($api === 1) {
                    $ticker = $this->getTickerFromCoinCheck($pair);
                } elseif ($api === 2) {
                    $ticker = $this->getTickerFromZaif($pair);
                } elseif ($api === 3) {
                    $ticker = $this->getTickerFromBitBank($pair);
                }
            } else {
                $ticker = '';
            }

            // 高値、安値の情報がある場合はDBに上書き
            if ($ticker) {
                echo ($pair . '<br>');
                echo ($ticker['high'] . '<br>');
                echo ($ticker['low'] . '<br>');
                $trend->high = $ticker['high'];
                $trend->low = $ticker['low'];
                $trend->updated_at = Carbon::now();
                $trend->save();
            }
        }
        return;
    }


    // ZaifAPIで過去24時間の最高取引価格、最安取引価格を取得
    public function getTickerFromZaif(string $currency_pair)
    {
        // Zaif API エンドポイントURL
        $endpoint = 'https://api.zaif.jp/api/1';

        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy', 'eth_jpy', 'xem_jpy', 'bch_jpy', 'mona_jpy'];

        // 通貨ペアのティッカーを取得
        // https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/4_ticker.html?highlight=ticker
        if (in_array($currency_pair, $active_pairs)) {
            // API URL: /ticker/{currency_pair}
            $url = $endpoint . '/ticker/' . $currency_pair;
            $ticker_json = file_get_contents($url);
            $ticker_obj = json_decode($ticker_json);
            $lists = [
                'high' => $ticker_obj->high, // 過去24時間の高値
                'low' => $ticker_obj->low, // 過去24時間の安値
            ];

            // $ticker キー一覧
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

    //  bitbank APIで過去24時間の最高取引価格、最安取引価格を取得
    public function getTickerFromBitBank(string $currency_pair)
    {
        // bitbank API エンドポイントURL
        $endpoint = 'https://public.bitbank.cc';

        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy', 'eth_jpy', 'xrp_jpy', 'ltc_jpy', 'mona_jpy', 'xlm_jpy'];

        // 通貨ペアのティッカーを取得
        // https://github.com/bitbankinc/bitbank-api-docs/blob/master/public-api_JP.md#ticker
        if (in_array($currency_pair, $active_pairs)) {
            // API URL: /{pair}/ticker
            $url = $endpoint . '/' . $currency_pair . '/ticker';
            $ticker_json = file_get_contents($url);
            $ticker_obj = json_decode($ticker_json);
            // dd($ticker_obj);

            // APIから返却されたステータスが成功であればデータを格納

            $lists = [
                'high' => $ticker_obj->data->high, // 過去24時間の高値
                'low' => $ticker_obj->data->low, // 過去24時間の安値
            ];

            // $ticker キー一覧
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
    public function getTickerFromCoinCheck(string $currency_pair)
    {
        // https://coincheck.com/ja/documents/exchange/api#ticker
        // coincheck API エンドポイントURL
        $endpoint = 'https://coincheck.com/api/ticker';
        // 取得可能な通貨ペア
        $active_pairs = ['btc_jpy'];

        if (in_array($currency_pair, $active_pairs)) {
            $url = $endpoint;
            $json_str = file_get_contents($url);
            $ticker = json_decode($json_str);
            $lists = [
                'high' => $ticker->high, // 過去24時間の高値
                'low' => $ticker->low, // 過去24時間の安値
            ];
        } else {
            $lists = '';
        }
        return $lists;
    }


    public function getTweet(string $keyword = '')
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
