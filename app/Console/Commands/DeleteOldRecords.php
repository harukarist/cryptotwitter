<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FetchPriceController;
use App\Http\Controllers\DeleteOldRecordsController;

class DeleteOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'delete:records';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = '保管期間を過ぎた古いツイート及び取得ログレコードをDBから削除';

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
        $deleteRecords = new DeleteOldRecordsController;
        logger()->info('>>>> レコード削除処理を実行します');
        $deleteRecords->deleteTweets();
        $deleteRecords->deleteFetchTweetsLogs();
        logger()->info('レコード削除処理を実行しました <<<<');
    }
}
