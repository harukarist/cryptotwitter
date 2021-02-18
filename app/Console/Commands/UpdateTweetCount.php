<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Trend;
use App\Tweet;
use Carbon\Carbon;

// テーブルに保存した仮想通貨関連ツイートから各銘柄の検索キーワードを含む件数を集計する
class UpdateTweetCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'update:tweetCount';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Update trends table set tweet numbers';


    /**
     * 更新処理に使用するプロパティ
     */
    protected $today;
    protected $words_arr = []; //検索キーワードを格納する配列
    protected $since_at; //集計期間の開始日時を指定
    protected $until_at; //集計期間の終了日時を指定

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->today = Carbon::now();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    // コマンドで実行する処理
    public function handle()
    {
        logger()->info('>>>> ツイート数の集計バッチ処理を実行します');

        // trendsテーブルから銘柄名と検索キーワードを配列型式で取得
        $trends_arr = Trend::select('currency_name', 'tweet_words')->get()->toArray();

        // 各銘柄について、検索キーワードが設定されていれば ORでつないだ文字列を分割し、銘柄名をキーとする配列に格納する
        foreach ($trends_arr as $trend) {
            if (array_key_exists('tweet_words', $trend)) {
                $this->words_arr[$trend['currency_name']] = explode(' OR ', $trend['tweet_words']);
            }
        }

        // 過去1時間の集計対象日時をセット
        $this->setHour();
        // 過去1時間の銘柄別ツイート数を集計
        $this->countUpdate('tweet_hou');

        // 過去24時間の集計対象日時をセット
        $this->setDay();
        // 過去24時間の銘柄別ツイート数を集計
        $this->countUpdate('tweet_day');

        // 過去1週間の集計対象日時をセット
        $this->setWeek();
        // 過去1週間の銘柄別ツイート数を集計
        $this->countUpdate('tweet_week');

        logger()->info('ツイート数の集計バッチ処理を実行しました <<<<');
    }

    /**
     * 集計開始日時・集計終了日時をセットするメソッド
     */
    public function setHour()
    {
        $this->since_at = $this->today->copy()->subHour(); //1時間前の日時をセット
        $this->until_at = $this->today->copy(); //現在時刻をセット
    }
    public function setDay()
    {
        $this->since_at = $this->today->copy()->subDay(); //1日前の日時をセット
        $this->until_at = $this->today->copy(); //現在時刻をセット
    }
    public function setWeek()
    {
        $this->since_at = $this->today->copy()->subWeek(); //1週間の日時をセット
        $this->until_at = $this->today->copy(); //現在時刻をセット
    }

    /**
     * 各銘柄の検索キーワードで指定期間のツイートを検索し、
     * キーワードを含むレコード数を取得するメソッド
     */
    public function countUpdate($column_name)
    {
        dump($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');
        logger()->info($this->since_at . ' 〜' . $this->until_at . 'の集計を開始');

        // 検索キーワードの配列をキー（銘柄名）とバリュー（検索キーワード）に展開して1つずつ処理
        foreach ($this->words_arr as $name => $keywords) {
            // 指定期間内のツイートを集計
            $count_num = Tweet::whereBetween('tweeted_at', [$this->since_at, $this->until_at])
                ->where(function ($query) use ($keywords) {
                    $i = 0;
                    foreach ($keywords as $keyword) {
                        // 1つ目のキーワードはwhere、2つ目以降はorWhereをクエリとしてLIKE句と"%キーワード%"で曖昧検索
                        $where = (!$i) ? 'where' : 'orWhere';
                        $i++;
                        $query->$where('tweet_text', 'LIKE', '%' . $keyword . '%');
                    }
                    // 重複するツイートレコードを除いて集計
                })->distinct('tweet_id')->count('tweet_id');

            // trendsテーブルの該当銘柄のカラム（1時間・1日・1週間のツイート数のいずれか）を更新
            Trend::where('currency_name', $name)->update([$column_name => $count_num]);
            dump($name . ' の集計値を ' . $count_num . ' に更新しました');
            logger()->info($name . ' の集計値を ' . $count_num . ' に更新しました');
        }
    }
}
