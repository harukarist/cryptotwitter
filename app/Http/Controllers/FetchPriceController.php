<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FetchPriceController extends Controller
{
    // 最高取引価格、最安取引価格を取得するAPIを呼び出し、DBに保存
    public function getPrices()
    {
        // Trendsテーブルから全レコードを取得
        $currency_records = Trend::all();

        // それぞれの仮想通貨についてループ処理を行う
        foreach ($currency_records as $currency_record) {
            $use_api = $currency_record->use_api; //APIを使用する場合は1,使用しない場合は0
            $pair = $currency_record->currency_pair; //通貨ペアの種類

            // 通貨ペア情報がDBに登録されていてAPI使用フラグが1の銘柄は、APIから最新価格を取得する
            if ($pair && $use_api) {
                // ZaifAPIから価格情報を取得
                $newest_price = $this->getPricesFromZaif($pair);
            } else {
                // 通貨ペア情報がDBに未登録またはAPI使用フラグが0の場合は空文字を格納する
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
                // テーブルの値を更新する
                logger()->info($currency_record->currency_name . 'の価格を更新');
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
        // API URL: /ticker/{currency_pair}
        if (in_array($currency_pair, $active_pairs)) {
            $url = $endpoint . '/ticker/' . $currency_pair;
            $prices_json = file_get_contents($url);
            $prices_obj = json_decode($prices_json);
            $lists = [
                'high' => $prices_obj->high, // 過去24時間の高値
                'low' => $prices_obj->low, // 過去24時間の安値
            ];
        } else {
            $lists = '';
        }
        return $lists;
    }
}
