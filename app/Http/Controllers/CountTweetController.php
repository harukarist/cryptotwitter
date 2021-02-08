<?php

namespace App\Http\Controllers;

use App\Trend;
use App\Tweet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountTweetController extends Controller
{
    public $words_arr = [];
    public $since_at = '';
    public $until_at = '';

    public function countTweet()
    {
        // trendsテーブルから銘柄名と検索キーワードを配列型式で取得
        $trends_arr = Trend::select('currency_name', 'tweet_words')->get()->toArray();

        // 各銘柄の検索キーワードをORでつないだ文字列から配列に分割
        foreach ($trends_arr as $trend) {
            $this->words_arr[$trend['currency_name']] = explode('OR', $trend['tweet_words']);
        }
        $this->countHourly();
        $this->countDaily();
        $this->countWeekly();
    }

    // 過去1時間の集計
    public function countHourly()
    {
        $dt = Carbon::now();
        $dt = $dt->subDays(1); //作業用
        $this->since_at = $dt->copy()->subHour(); //1時間前の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
        $this->countUpdate('tweet_hour'); //集計値を更新
    }

    // 過去1日の集計
    public function countDaily()
    {
        $dt = Carbon::now();
        $dt = $dt->subDays(1); //作業用
        $this->since_at = $dt->copy()->subDay(); //1日前の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
        $this->countUpdate('tweet_day'); //集計値を更新
    }
    // 過去１週間の集計
    public function countWeekly()
    {
        $dt = Carbon::now();
        $dt = $dt->subDays(1); //作業用
        $this->since_at = $dt->copy()->subWeek(); //1日前の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
        $this->countUpdate('tweet_week'); //集計値を更新
    }

    // 各銘柄の検索キーワードで指定期間のツイートを検索し、キーワードを含むレコード数を取得
    public function countUpdate($column_name)
    {
        echo  $column_name . ' カラムの値を更新<br>';
        logger()->info($column_name . ' カラムの値を更新');
        foreach ($this->words_arr as $name => $keywords) {
            $count_num = Tweet::whereBetween('tweeted_at', [$this->since_at, $this->until_at])
                ->where(function ($query) use ($keywords) {
                    $i = 0;
                    foreach ($keywords as $keyword) {
                        $where = (!$i) ? 'where' : 'orWhere';
                        $i++;
                        $query->$where('tweet_text', 'LIKE', '%' . $keyword . '%');
                    }
                })->count();
            Trend::where('currency_name', $name)->update([$column_name => $count_num]);
            echo  $name . ' の集計値を ' . $count_num . ' に更新しました<br>';
            logger()->info($name . ' の集計値を ' . $count_num . ' に更新しました');
        }
    }
}
    // 取得済みツイートのレコードから、キーワードを含むレコード数をカウント
    // $test1 = Tweet::where('tweet_text', 'LIKE', "%BTC%")
    //     ->where('tweeted_at', '>=', $since_at)
    //     ->where('tweeted_at', '<=', $until_at)
    //     ->count();
    // $test2 = Tweet::where('tweeted_at', '>=', $since_at)
    //     ->where('tweeted_at', '<=', $until_at)
    //     ->where('tweet_text', 'LIKE', "%ビットコイン%")
    //     ->count();

    // $test3 = Tweet::where('tweet_text', $key)
    //     ->where('tweeted_at', '>=', $since_at)
    //     ->where('tweeted_at', '<=', $until_at)
    //     ->where(function ($query) {
    //         $query->where('tweet_text', 'LIKE', "%BTC%")
    //             ->orWhere('tweet_text', 'LIKE', "%ビットコイン%");
    //     })->count();

    // dd($test3);
    // Trend::where('currency_name', $key)->update(['tweet_hour' => $count_num[$key]]);
    // echo  $key . ' 過去1hの集計結果:' . $count_num[$key] . "<br>";
    // logger()->info($key . ' 過去1hの集計結果:' . $count_num[$key]);
    // return;
    // }
// }
