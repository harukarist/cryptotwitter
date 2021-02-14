<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Batch;
use App\Trend;
use Carbon\Carbon;

// 最高取引価格、最安取引価格を取得するZaifAPIを呼び出し、DBに保存する処理
class UpdatePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'update:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Zaif APIで仮想通貨銘柄毎の最高取引価格、最安取引価格を取得し、DBに保存';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    // コマンドで実行する処理
    public function handle()
    {
        logger()->info('>>>> 取引価格の更新バッチ処理を実行します');
        // trendsテーブルから仮想通貨の各銘柄レコードを取得
        $trends = Trend::all();

        // 仮想通貨の各銘柄についてループ処理を行う
        foreach ($trends as $trend) {
            $use_api = $trend->use_api; //APIを使用する場合は1,使用しない場合は0
            $pair = $trend->currency_pair; //通貨ペアの種類

            // 通貨ペア情報がDBに登録されていてAPI使用フラグが1の銘柄は、最新価格を取得する
            if ($pair && $use_api) {
                // ZaifAPIから価格情報を取得
                $newest_price = $this->getPricesFromZaif($pair);
            } else {
                // 通貨ペア情報がDBに未登録またはAPI使用フラグが0の場合は取引価格に空文字を格納する
                $newest_price = '';
            }

            $table_updated = false; //DB更新の有無を判定するフラグ

            // 最高取引価格があり、かつDBの最高取引価格と異なる場合
            if ($newest_price && $trend->high <> $newest_price['high']) {
                // その通貨の最高取引価格を上書きする
                $trend->high = $newest_price['high'];
                // ヘルパー関数logger()でログを出力
                dump("{$trend->currency_name}の最高取引価格を{$newest_price['high']}に更新");
                logger()->info("{$trend->currency_name}の最高取引価格を{$newest_price['high']}に更新");
                //更新フラグをtrueに変更
                $table_updated = true;
            }
            // 最安取引価格があり、かつDBの最安取引価格と異なる場合
            if ($newest_price && $trend->low <> $newest_price['low']) {
                // その通貨の最低取引価格を上書きする
                $trend->low = $newest_price['low'];
                // ヘルパー関数logger()でログを出力
                dump("{$trend->currency_name}の最安取引価格を{$newest_price['low']}に更新");
                logger()->info("{$trend->currency_name}の最安取引価格を{$newest_price['low']}に更新");
                //更新フラグをtrueに変更
                $table_updated = true;
            }
            // 更新フラグがtrueの場合
            if ($table_updated) {
                // テーブルの値を更新する
                $trend->save();
            }
        }

        // API実行日時をバッチテーブルに保存（batch_nameカラムの値がupdate_pricesのレコードがあれば更新、なければレコードを新規作成）
        Batch::updateOrCreate(
            ['batch_name' => 'update_prices'],
            ['batch_finished_at' => Carbon::now()],
        );

        return;
        logger()->info('取引価格の更新バッチ処理を実行しました <<<<');
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
