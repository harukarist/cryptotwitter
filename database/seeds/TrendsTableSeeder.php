<?php

use App\Trend;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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
                'use_api' => 1,
                'tweet_words' => 'BTC OR ビットコイン'
            ],
            [
                'currency_name' => 'ETH',
                'currency_ja' => 'イーサリアム',
                'currency_pair' => 'eth_jpy',
                'use_api' => 1,
                'tweet_words' => 'ETH OR イーサリアム'
            ],
            [
                'currency_name' => 'ETC',
                'currency_ja' => 'イーサリアムクラシック',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'ETC OR イーサリアムクラシック'
            ],
            [
                'currency_name' => 'LSK',
                'currency_ja' => 'リスク',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'LSK'
            ],
            [
                'currency_name' => 'FCT',
                'currency_ja' => 'ファクトム',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'FCT OR ファクトム'
            ],
            [
                'currency_name' => 'XRP',
                'currency_ja' => 'リップル',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'XRP OR リップル'
            ],
            [
                'currency_name' => 'XEM',
                'currency_ja' => 'ネム',
                'currency_pair' => 'xem_jpy',
                'use_api' => 1,
                'tweet_words' => 'XEM OR ネム'
            ],
            [
                'currency_name' => 'LTC',
                'currency_ja' => 'ライトコイン',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'LTC OR ライトコイン'
            ],
            [
                'currency_name' => 'BCH',
                'currency_ja' => 'ビットコインキャッシュ',
                'currency_pair' => 'bch_jpy',
                'use_api' => 1,
                'tweet_words' => 'BCH OR ビットコインキャッシュ'
            ],
            [
                'currency_name' => 'MONA',
                'currency_ja' => 'モナコイン',
                'currency_pair' => 'mona_jpy',
                'use_api' => 1,
                'tweet_words' => 'MONA OR モナコイン'
            ],
            [
                'currency_name' => 'XLM',
                'currency_ja' => 'ステラルーメン',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'XLM OR ステラルーメン'
            ],
            [
                'currency_name' => 'QTUM',
                'currency_ja' => 'クアンタム',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'QTUM OR クアンタム'
            ],
            [
                'currency_name' => 'BAT',
                'currency_ja' => 'ベーシックアテンショントークン',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'BAT OR ベーシックアテンショントークン'
            ],
            [
                'currency_name' => 'IOST',
                'currency_ja' => 'アイオーエスティー',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'IOST OR アイオーエスティー'
            ],
            [
                'currency_name' => 'DASH',
                'currency_ja' => 'ダッシュ',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'DASH'
            ],
            [
                'currency_name' => 'ZEC',
                'currency_ja' => 'ジーキャッシュ',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'ZEC OR ジーキャッシュ'
            ],
            [
                'currency_name' => 'XMR',
                'currency_ja' => 'モネロ',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'XMR OR モネロ'
            ],
            [
                'currency_name' => 'REP',
                'currency_ja' => 'オーガー',
                'currency_pair' => '',
                'use_api' => 0,
                'tweet_words' => 'REP OR オーガー'
            ],
        ];

        foreach ($trends as $trend) {
            Trend::insert([
                'currency_name' => $trend['currency_name'],
                'currency_ja' => $trend['currency_ja'],
                'currency_pair' => $trend['currency_pair'],
                'use_api' => $trend['use_api'],
                'tweet_words' => $trend['tweet_words'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        };
    }
}
