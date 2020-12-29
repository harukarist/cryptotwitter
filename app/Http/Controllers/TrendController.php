<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrendController extends Controller
{
    // 各銘柄の24時間での最高取引価格、最安取引価格を取得
    public function getTicker()
    {
        // coincheckで取得
        // API doc : https://coincheck.com/ja/documents/exchange/api#ticker
        // API url : https://coincheck.com/api/ticker
        $url = "https://coincheck.com/api/ticker";
        $json_str = file_get_contents($url);
        $json_decode['coincheck'] = json_decode($json_str);

        // {"last":2813627.0,"bid":2813628.0,"ask":2814941.0,"high":2882498.0,"low":2670000.0,"volume":6882.37168428,"timestamp":1609160664}

        // bitflyer で取得
        // https://lightning.bitflyer.com/docs/api?lang=ja#ticker
        $json_str = file_get_contents("https://api.bitflyer.jp/v1/markets");
        $json_decode['markets'] = json_decode($json_str);
        $json_str = file_get_contents("https://api.bitflyer.jp/v1/ticker?product_code=BTC_JPY");
        $json_decode['bitflyer'] = json_decode($json_str);


        // Zaif で取得
        // https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/2_currency_pairs.html#public-currency-pairs
        // https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/4_ticker.html?highlight=ticker
        // https://api.zaif.jp/api/1/ticker/ticker/{currency_pair}

        $json_str = file_get_contents("https://api.zaif.jp/api/1/currency_pairs/all");
        $currencies = json_decode($json_str);

        foreach ($currencies as $currency) {
            
            $url = 'https://api.zaif.jp/api/1/ticker/' . $currency->currency_pair;
            $json_str = file_get_contents($url);
            $json_decode = json_decode($json_str);
            $list[] = [
                'name' => $currency->name, // Todo：後ろに/JPYがつくcurrency_pairのみ対象とする
                'description' => $currency->description,
                'high' => $json_decode->high,
                'low' => $json_decode->low,
            ];
            echo $currency->name . " ";
            echo "¥" . number_format($json_decode->high) . " ";
            echo "¥" . number_format($json_decode->low) . "<br>";
            // キー	詳細	型
            // last	終値	float
            // high	過去24時間の高値	float
            // low	過去24時間の安値	float
            // vwap	過去24時間の加重平均	float
            // volume	過去24時間の出来高	float
            // bid	買気配値	float
            // ask	売気配値	float
        }

        // dd($currencies, $list);
        return view('home');
        }
}
