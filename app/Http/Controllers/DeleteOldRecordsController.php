<?php

namespace App\Http\Controllers;

use App\Tweet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class DeleteOldRecordsController extends Controller
{
    protected $today;
    protected $last_date;

    function __construct()
    {
        $storage_days = 8; //ツイートを保存しておく日数
        $this->today = Carbon::today();
        $this->last_date = $this->today->copy()->subDays($storage_days);
    }

    // 保存期間を過ぎた古いツイートレコードをテーブルから削除する
    public function deleteTweets()
    {

        $deleted = Tweet::where('tweeted_at', '<', $this->last_date)->delete();
        echo ("{$this->last_date}以前のツイートレコードを{$deleted}件削除しました<br>");
        logger()->info("{$this->last_date}以前のツイートレコードを{$deleted}件削除しました");
    }
    // 保存期間を過ぎた古いツイート取得ログレコードをテーブルから削除する
    public function deleteFetchTweetsLogs()
    {

        $deleted = DB::table('fetch_tweets_logs')->where('since_at', '<', $this->last_date)->delete();
        echo ("{$this->last_date}以前のツイート取得ログレコードを{$deleted}件削除しました<br>");
        logger()->info("{$this->last_date}以前のツイート取得ログレコードを{$deleted}件削除しました");
    }
}
