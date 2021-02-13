<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FetchTargetTweetController;

class FetchTargetsTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:targetsTweet';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Fetch TwitterUsers Tweet from TwitterAPI and Insert target_users table';

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
        FetchTargetTweetController::fetchLatestTweet();
    }
}
