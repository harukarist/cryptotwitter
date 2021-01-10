<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceController extends Controller
{
    // 最高取引価格、最安取引価格を取得するAPIを呼び出し、DBに保存
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

            // APIから最高取引価格を取得しており、かつDBの最高取引価格と異なる場合
            if ($newest_price && $currency_record->high <> $newest_price['high']) {
                // その通貨の最高取引価格を上書きする
                $currency_record->high = $newest_price['high'];
                // ヘルパー関数logger()でログを出力
                logger()->info('high:' . $currency_record->high . '-' . $currency_record->currency_name);
                //更新フラグをtrueに変更
                $table_updated = true;
            }
            // APIから最安取引価格を取得しており、かつDBの最安取引価格と異なる場合
            if ($newest_price && $currency_record->low <> $newest_price['low']) {
                // その通貨の最低取引価格を上書きする
                $currency_record->low = $newest_price['low'];
                // ヘルパー関数logger()でログを出力
                logger()->info('low:' . $currency_record->low . '-' . $currency_record->currency_name);
                //更新フラグをtrueに変更
                $table_updated = true;
            }
            // 更新フラグがtrueの場合
            if ($table_updated) {
                // 更新日時に現在日時を代入
                // $currency_record->updated_at = Carbon::now();
                // テーブルの値を更新する
                logger()->info($currency_record->currency_name . 'の価格を更新' . $currency_record->updated_at); // ヘルパー関数logger()でログを出力
                $currency_record->save();
            }
        }

        // API実行日時をバッチテーブルに保存（batch_nameカラムの値がupdate_pricesのレコードがあれば更新、なければレコードを新規作成）
        Batch::updateOrCreate(
            ['batch_name' => 'update_prices'],
            ['batch_finished_at' => Carbon::now()],
        );

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
        // API Doc: https://zaif-api-document.readthedocs.io/ja/latest/PublicAPI.html#id22
        // API Doc: https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/4_ticker.html
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
}
