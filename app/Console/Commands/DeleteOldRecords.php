<?php

namespace App\Console\Commands;

use App\Tweet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


/**
 * 所定の保管期間を過ぎた古いレコードをDBから削除するコマンド
 */
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
    protected $description = '所定の保管期間を過ぎた古いレコードをDBから削除';

    /**
     * 削除処理に使用するプロパティ
     */
    protected $today;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // バッチ実行日の0:00の日時を取得
        $this->today = Carbon::today();
    }

    /**
     * Execute the console command.
     * コマンドで実行するメソッド
     * @return mixed
     */
    public function handle()
    {
        logger()->info('>>>> レコード削除処理を実行します');
        $this->deleteTweets();
        $this->deleteFetchTweetsLogs();
        $this->deleteFetchTargetsLogs();
        $this->deleteAutoFollowLogs();
        logger()->info('レコード削除処理を実行しました <<<<');
    }

    /**
     * 保存期間を過ぎた古いツイートレコードをテーブルから削除するメソッド
     */
    public function deleteTweets()
    {
        $STORAGE_DAYS = 8; //レコードを保存しておく日数
        $storage_started = $this->today->copy()->subDays($STORAGE_DAYS);
        $deleted = Tweet::where('tweeted_at', '<', $storage_started)->delete();
        dump("{$storage_started}以前のツイートレコードを{$deleted}件削除しました");
        logger()->info("{$storage_started}以前のツイートレコードを{$deleted}件削除しました");
    }

    /**
     * 保存期間を過ぎた古いツイート取得ログレコードをテーブルから削除するメソッド
     */
    public function deleteFetchTweetsLogs()
    {
        $STORAGE_DAYS = 8; //レコードを保存しておく日数
        $storage_started = $this->today->copy()->subDays($STORAGE_DAYS);
        $deleted = DB::table('fetch_tweets_logs')->where('since_at', '<', $storage_started)->delete();
        dump("{$storage_started}以前のツイート取得ログレコードを{$deleted}件削除しました");
        logger()->info("{$storage_started}以前のツイート取得ログレコードを{$deleted}件削除しました");
    }

    /**
     * 保存期間を過ぎた古いターゲットユーザー取得ログレコードをテーブルから削除するメソッド
     */
    public function deleteFetchTargetsLogs()
    {
        $STORAGE_DAYS = 8; //レコードを保存しておく日数
        $storage_started = $this->today->copy()->subDays($STORAGE_DAYS);
        $deleted = DB::table('fetch_targets_logs')->where('created_at', '<', $storage_started)->delete();
        dump("{$storage_started}以前のターゲットユーザー取得ログレコードを{$deleted}件削除しました");
        logger()->info("{$storage_started}以前のターゲットユーザー取得ログレコードを{$deleted}件削除しました");
    }

    /**
     * 保存期間を過ぎた古い自動フォローログレコードをテーブルから削除するメソッド
     */
    public function deleteAutoFollowLogs()
    {
        $STORAGE_DAYS = 8; //レコードを保存しておく日数
        $storage_started = $this->today->copy()->subDays($STORAGE_DAYS);
        $deleted = DB::table('autofollow_logs')->where('created_at', '<', $storage_started)->delete();
        dump("{$storage_started}以前の自動フォローログログレコードを{$deleted}件削除しました");
        logger()->info("{$storage_started}以前の自動フォローログログレコードを{$deleted}件削除しました");
    }
}
