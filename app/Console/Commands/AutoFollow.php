<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AutoFollowController;

class AutoFollow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'follow:autofollow';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Follow Target accounts from Users twitter accounts';

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
        $follow = new AutoFollowController;
        logger()->info('>>>> 自動フォロー処理バッチを実行します');
        $follow->autoFollow();
        //ログファイルに書き込む
        logger()->info('自動フォロー処理バッチを実行しました <<<<');
    }
}
