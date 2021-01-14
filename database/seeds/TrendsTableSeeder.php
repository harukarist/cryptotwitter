<?php

use App\Trend;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrendsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trends = [
            [
                'currency_name' => 'BTC',
                'currency_ja' => 'ビットコイン',
                'currency_pair' => 'btc_jpy',
                'used_api_type' => 2,
            ],
            [
                'currency_name' => 'ETH',
                'currency_ja' => 'イーサリアム',
                'currency_pair' => 'eth_jpy',
                'used_api_type' => 2,
            ],
            [
                'currency_name' => 'ETC',
                'currency_ja' => 'イーサリアムクラシック',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'LSK',
                'currency_ja' => 'リスク',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'FCT',
                'currency_ja' => 'ファクトム',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'XRP',
                'currency_ja' => 'リップル',
                'currency_pair' => 'xrp_jpy',
                'used_api_type' => 3,
            ],
            [
                'currency_name' => 'XEM',
                'currency_ja' => 'ネム',
                'currency_pair' => 'xem_jpy',
                'used_api_type' => 2,
            ],
            [
                'currency_name' => 'LTC',
                'currency_ja' => 'ライトコイン',
                'currency_pair' => 'ltc_jpy',
                'used_api_type' => 3,
            ],
            [
                'currency_name' => 'BCH',
                'currency_ja' => 'ビットコインキャッシュ',
                'currency_pair' => 'bch_jpy',
                'used_api_type' => 2,
            ],
            [
                'currency_name' => 'MONA',
                'currency_ja' => 'モナコイン',
                'currency_pair' => 'mona_jpy',
                'used_api_type' => 2,
            ],
            [
                'currency_name' => 'XLM',
                'currency_ja' => 'ステラルーメン',
                'currency_pair' => 'xlm_jpy',
                'used_api_type' => 3,
            ],
            [
                'currency_name' => 'QTUM',
                'currency_ja' => 'クアンタム',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'BAT',
                'currency_ja' => 'ベーシックアテンショントークン',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'IOST',
                'currency_ja' => 'アイオーエスティー',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'DASH',
                'currency_ja' => 'ダッシュ',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'ZEC',
                'currency_ja' => 'ジーキャッシュ',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'XMR',
                'currency_ja' => 'モネロ',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
            [
                'currency_name' => 'REP',
                'currency_ja' => 'オーガー',
                'currency_pair' => '',
                'used_api_type' => 0,
            ],
        ];

        foreach ($trends as $trend) {
            $words = $trend['currency_name'] . ' OR ' . $trend['currency_ja'];
            Trend::insert([
                'currency_name' => $trend['currency_name'],
                'currency_ja' => $trend['currency_ja'],
                'currency_pair' => $trend['currency_pair'],
                'used_api_type' => $trend['used_api_type'],
                'tweet_words' => $words,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
