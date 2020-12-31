<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// ティッカー情報確認機能（管理者用）
class TickerController extends Controller
{
    // ティッカー一覧表示処理
    public function index()
    {
        $tickers['zaif'] = $this->getTickerFromZaif();
        $tickers['bitbank'] = $this->getTickerFromBitBank();
        $tickers['coincheck'] = $this->getTickerFromCoinCheck();

        // JSON形式に変換してビューに渡す
        $tickers_json = json_encode($tickers);
        // dd($tickers_json);

        return $tickers_json;
    }

    // Zaif APIで過去24時間の最高取引価格、最安取引価格を取得
    public function getTickerFromZaif()
    {
        // Zaif API エンドポイントURL
        $endpoint = 'https://api.zaif.jp/api/1';

        // 取得可能な通貨ペア
        $currency_pairs = ['btc_jpy', 'eth_jpy', 'xem_jpy', 'bch_jpy', 'mona_jpy'];

        // それぞれの通貨ペアのティッカーを取得
        // https://techbureau-api-document.readthedocs.io/ja/latest/public/2_individual/4_ticker.html?highlight=ticker
        foreach ($currency_pairs as $currency_pair) {
            // API URL: /ticker/{currency_pair}
            $url = $endpoint . '/ticker/' . $currency_pair;
            $ticker_json = file_get_contents($url);
            $ticker_obj = json_decode($ticker_json);
            $lists[] = [
                'name' => $currency_pair,
                'high' => number_format($ticker_obj->high), // 過去24時間の高値
                'low' => number_format($ticker_obj->low), // 過去24時間の安値
                'last' => number_format($ticker_obj->last),
                'volume' => number_format($ticker_obj->volume),
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
        }
        return $lists;
    }

    //  bitbank APIで過去24時間の最高取引価格、最安取引価格を取得
    public function getTickerFromBitBank()
    {
        // bitbank API エンドポイントURL
        $endpoint = 'https://public.bitbank.cc';

        // 取得可能な通貨ペア
        $currency_pairs = ['btc_jpy', 'eth_jpy', 'xrp_jpy', 'ltc_jpy', 'mona_jpy', 'xlm_jpy'];

        // それぞれの通貨ペアのティッカーを取得
        // https://github.com/bitbankinc/bitbank-api-docs/blob/master/public-api_JP.md#ticker
        foreach ($currency_pairs as $currency_pair) {
            // API URL: /{pair}/ticker
            $url = $endpoint . '/' . $currency_pair . '/ticker';
            $ticker_json = file_get_contents($url);
            $ticker_obj = json_decode($ticker_json);
            // dd($ticker_obj);

            // APIから返却されたステータスが成功であればデータを格納
            if ($ticker_obj->success === 1) {
                $lists[] = [
                    'name' => $currency_pair,
                    'high' => number_format($ticker_obj->data->high), // 過去24時間の高値
                    'low' => number_format($ticker_obj->data->low), // 過去24時間の安値
                    'last' => number_format($ticker_obj->data->last),
                    'volume' => number_format($ticker_obj->data->vol),
                ];
            }

            // $ticker キー一覧
            // Name	Type	Description
            // sell	string	現在の売り注文の最安値
            // buy	string	現在の買い注文の最高値
            // high	string	過去24時間の最高値取引価格
            // low	string	過去24時間の最安値取引価格
            // last	string	最新取引価格
            // vol	string	過去24時間の出来高
            // timestamp	number	日時（UnixTimeのミリ秒）
        }
        return $lists;
    }

    // coincheckで過去24時間の最高取引価格、最安取引価格を取得
    public function getTickerFromCoinCheck()
    {
        // // coincheckで取得
        // // https://coincheck.com/ja/documents/exchange/api#ticker
        $url = "https://coincheck.com/api/ticker";
        $json_str = file_get_contents($url);
        $ticker = json_decode($json_str);
        $lists[] = [
            'high' => number_format($ticker->high), // 過去24時間の高値
            'low' => number_format($ticker->low), // 過去24時間の安値
        ];
        return $lists;
    }
}
