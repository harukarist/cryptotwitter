<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PriceController;

class UpdatePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Command名
    protected $signature = 'update:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Update trends table set high and low price';

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
    // 実行したい処理
    public function handle()
    {
        // コントローラーのタスクを実行する
        $trend = new PriceController;
        logger()->info('>>>> 取引価格の更新バッチ処理を実行します');
        $trend->getPrices();
        //ログファイルに書き込む
        logger()->info('取引価格の更新バッチ処理を実行しました <<<<');
    }
}
