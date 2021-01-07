<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FetchTwitterUserController;

class FetchUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Command名
    protected $signature = 'fetch:users';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Fetch TwitterUsers from TwitterAPI and Insert target_users table';

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
        $user = new FetchTwitterUserController;
        logger()->info('>>>> Twitterユーザー保存バッチを実行します');
        $user->fetchUsers();
        //ログファイルに書き込む
        logger()->info('Twitterユーザー保存バッチを実行しました <<<<');
    }
}
