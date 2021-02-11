<?php

namespace App\Http\Controllers;

use App\Trend;
use App\Tweet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// テーブルに保存した仮想通貨関連ツイートから各銘柄の検索キーワードを含む件数を集計する
class CountTweetController extends Controller
{
    public $words_arr = []; //検索キーワードを格納する配列
    public $since_at = ''; //集計期間の開始日時を指定
    public $until_at = ''; //集計期間の終了日時を指定

    // 検索キーワードの取得、集計、保存を実行
    public function countTweet()
    {
        // trendsテーブルから銘柄名と検索キーワードを配列型式で取得
        $trends_arr = Trend::select('currency_name', 'tweet_words')->get()->toArray();

        // 各銘柄の検索キーワードをORでつないだ文字列を分割し、銘柄名をキーとする配列に格納する
        foreach ($trends_arr as $trend) {
            $this->words_arr[$trend['currency_name']] = explode(' OR ', $trend['tweet_words']);
        }
        clock($this->words_arr);
        // 過去1時間、過去1日、過去1週間の集計を実行
        $this->setHour();
        $this->countUpdate('tweet_hour');

        $this->setDay();
        $this->countUpdate('tweet_day');

        $this->setWeek();
        $this->countUpdate('tweet_week');
    }

    // 過去1時間の集計
    public function setHour()
    {
        $dt = Carbon::now();
        $this->since_at = $dt->copy()->subHour(); //1時間前の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
    }

    // 過去1日の集計
    public function setDay()
    {
        $dt = Carbon::now();
        $this->since_at = $dt->copy()->subDay(); //1日前の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
    }
    // 過去１週間の集計
    public function setWeek()
    {
        $dt = Carbon::now();
        $this->since_at = $dt->copy()->subWeek(); //1週間の日時をセット
        $this->until_at = $dt->copy(); //現在時刻をセット
        echo  $this->since_at . ' 〜' . $this->until_at . 'の集計を開始<br>';
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
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
                        // 最初のキーワードはwhere、2つ目以降はorWhereをクエリとする
                        $where = (!$i) ? 'where' : 'orWhere';
                        $i++;
                        $query->$where('tweet_text', 'LIKE', '%' . $keyword . '%');
                        clock($query);
                    }
                })->count();

            // trendsテーブルの該当銘柄のカラム（1時間・1日・1週間のツイート数のいずれか）を更新
            Trend::where('currency_name', $name)->update([$column_name => $count_num]);
            echo  $name . ' の集計値を ' . $count_num . ' に更新しました<br>';
            logger()->info($name . ' の集計値を ' . $count_num . ' に更新しました');
        }
    }
}
