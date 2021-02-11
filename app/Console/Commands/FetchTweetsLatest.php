<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FetchTweetController;

class FetchTweetsLatest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:latestTweets';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Fetch latest tweets from TwitterAPI and Insert tweets table';

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
        $tweet = new FetchTweetController;
        logger()->info('>>>> ツイート保存バッチを実行します');
        $tweet->fetchLatestTweets();
        //ログファイルに書き込む
        logger()->info('ツイート保存バッチを実行しました <<<<');
    }
}
