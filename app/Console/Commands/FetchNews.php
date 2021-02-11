<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FetchNewsController;

class FetchNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:news';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Fetch GoogleNews from GoogleAPI and Insert news_lists table';

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
        $news = new FetchNewsController;
        logger()->info('>>>> GoogleNews保存バッチを実行します');
        $news->fetchNews();
        //ログファイルに書き込む
        logger()->info('GoogleNews保存バッチを実行しました <<<<');
    }
}
