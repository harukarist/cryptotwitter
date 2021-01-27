<?php

namespace App\Console\Commands;

use App\Http\Controllers\CountTweetController;
use Illuminate\Console\Command;

class UpdateTweetNum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Command名
    protected $signature = 'update:tweetNum';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Update trends table set tweet numbers';

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
        $tweet = new CountTweetController;
        logger()->info('>>>> ツイート数の集計バッチ処理を実行します');
        $tweet->countTweet();
        //ログファイルに書き込む
        logger()->info('ツイート数の集計バッチ処理を実行しました <<<<');
    }
}
