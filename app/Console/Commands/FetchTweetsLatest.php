<?php

namespace App\Console\Commands;

use App\Trend;
use App\Tweet;
use Carbon\Carbon;
use App\Facades\Twitter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Abraham\TwitterOAuth\TwitterOAuthException;

/**
 * 仮想通貨トレンド表示で使用する、各銘柄のツイート数の集計元となる
 * 仮想通貨関連のツイートをTwitterAPIで取得し、tweetsテーブルに保存するコマンド
 */
class FetchTweetsLatest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:latestTweets';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'キーワードを含む直近のツイートをTwitterAPIから取得し、DBに保存';

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
     * コマンドで実行するメソッド
     * @return mixed
     */
    public function handle()
    {
        /**
         * fetch_tweets_logsテーブルのログレコードから未取得の時間帯を調べ、
         * 10分毎に時間帯を区切って過去の時間帯から順に取得する。
         * TwitterAPIでは、1つの時間帯の中で新しいツイートから古いツイートの順
         * （ツイートIDの降順）にツイートを取得する。
         * 全ツイートを取得しきれなかった場合は、検索対象となった最も古いツイートIDが
         * max_idとしてTwitterAPIから返却されるため、
         * fetch_tweets_logsテーブルのnext_idカラムにmax_idを保存しておき、
         * 次のリクエスト時にそのmax_idをパラメータに指定して
         * さらに古いツイートを取得する。
         */

        // ツイート検索のリクエスト上限の初期値（API指定の上限は15分間に180回）
        $MAX_REQUEST = 180;

        //ログファイルに書き込む
        logger()->info('>>>> ツイート保存バッチを実行します');

        // TwitterAPIのリクエスト残り回数を取得するメソッドを実行
        $limit_count = $this->checkLimit();

        // リクエスト上限が0以下の場合は処理を終了
        if ($limit_count <= 0) {
            dump("ツイート検索のリクエスト上限に達しました");
            logger()->info("ツイート検索のリクエスト上限に達しました");
            return;
        }

        // リクエスト残り回数が初期値より少なければ、リクエスト残り回数をリクエスト回数とする
        if ($limit_count < $MAX_REQUEST) {
            $remain_count = $limit_count;
        } else {
            // リクエスト残り回数が初期値より大きければ、初期値をリクエスト回数とする
            $remain_count = $MAX_REQUEST;
        }
        dump("リクエスト回数はあと" . $remain_count . "回");
        logger()->info("リクエスト回数はあと" . $remain_count . "回");

        // APIの残り使用可能回数が0になるまで、または10分前のツイートを取得できるまでループ
        for ($i = 0; true; $i++) {
            // 最新日時のツイート取得ログデータ1件をDBから取得
            $log = DB::table('fetch_tweets_logs')->orderBy('until_at', 'DESC')->first();
            // ログデータがない場合
            if (!$log) {
                // 以降の処理は行わず、1週間前から1時間毎にツイートを取得するメソッドを実行
                Artisan::call('fetch:weeklyTweets');
                return;
            }

            // ログデータがある場合
            $last_since = new Carbon($log->since_at); //最終ログデータの開始日時
            $last_until = new Carbon($log->until_at); //最終ログデータの終了日時
            $carbon_now = Carbon::now(); // 現在日時を取得

            // 最終ログデータの終了日時と現在時刻の差が10分未満の場合
            if ($last_until->diffInMinutes($carbon_now) < 10) {
                // 全てのツイートが取得できるよう、10分単位となるまで処理を行わずに終了
                dump("直近の未取得ツイートはありません");
                logger()->info("直近の未取得ツイートはありません");
                break;
            }

            // ログデータのnext_idカラムに値がある場合
            if ($log->next_id) {
                // next_id（次回の取得予定ID）をAPIパラメータのmax_id（取得開始id)に指定して、ログデータと同じ時間帯でmax_idより前のツイートを取得する
                $since_at = $last_since; //ログデータと同じ開始日時から検索
                $until_at = $last_until; //ログデータと同じ終了日時まで検索
                // 検索開始日時、終了日時を指定して検索パラメータを生成するメソッドを実行
                $params = $this->getParams($since_at, $until_at);
                $params['max_id'] = $log->next_id; //next_idをAPIのmax_id（取得開始id)に指定
            } else {
                // next_idカラムが空の場合はログデータの直後10分間のツイートを取得する
                $since_at = $last_until; //ログデータの終了日時から検索
                $until_at = $last_until->copy()->addMinutes(10); //ログデータの終了日時から10分後まで検索

                // 検索開始日時、終了日時を指定して検索パラメータを生成するメソッドを実行
                $params = $this->getParams($since_at, $until_at);
            }

            dump($since_at . "〜" . $until_at . "のツイートを取得");
            logger()->info($since_at . "〜" . $until_at . "のツイートを取得");

            // TwitterAPIでツイートを検索し、該当データを保存するメソッドを実行
            [$total_count, $max_id, $req_count] = $this->requestAndSaveTweets($remain_count, $params);

            // 対象時間帯のログをDBに保存
            DB::table('fetch_tweets_logs')->insert([
                'since_at' => $since_at,
                'until_at' => $until_at,
                'total_count' => $total_count,
                'begin_id' => $log->next_id ?? '',
                'next_id' => $max_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            dump($req_count . "回リクエスト済み");
            logger()->info($req_count . "回リクエスト済み");

            // 最大リクエスト回数から今回のリクエスト回数を減らす
            $remain_count -= $req_count;

            // 残り使用可能回数が0以下なら処理を終了
            if ($remain_count <= 0) {
                dump("残り回数が" . $remain_count . "のため処理を終了");
                logger()->info("残り回数が" . $remain_count . "のため処理を終了");
                return;
            }
        }

        logger()->info('ツイート保存バッチを実行しました <<<<');
    }



    /**
     * TwitterAPIでレートリミットを取得するメソッド
     */
    public function checkLimit()
    {
        //残り使用可能回数をTwitterAPIでチェック
        $status = Twitter::get("application/rate_limit_status");

        // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
        if (property_exists($status, 'errors')) {
            dump("残り使用可能回数が取得できませんでした");
            logger()->info("残り使用可能回数が取得できませんでした");
            return 0;
        }

        // 検索APIの残り使用可能回数が存在する場合は回数の値を取得
        if (property_exists($status, 'resources')) {
            $limit_obj = $status->resources->search;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('/search/tweets', $limit_arr)) {
                if (property_exists($limit_arr['/search/tweets'], 'remaining')) {
                    $limit_count = $limit_arr['/search/tweets']->remaining; // 残り使用回数
                    dump("ツイート検索のAPI使用可能回数:{$limit_count}回");
                    logger()->info("ツイート検索のAPI使用可能回数:{$limit_count}回");
                    return $limit_count;
                }
            }
        }
        // その他の場合は0を返却
        return 0;
    }


    /**
     * 検索用パラメーターを生成するメソッド
     */
    public function getParams($since_at, $until_at)
    {
        // trendsテーブルから検索キーワードのカラム'tweet_words'を取得
        $words_arr = Trend::select('tweet_words')->get();
        // 検索キーワードのレコードを OR で連結して1つの文字列にする
        $keywords = $words_arr->implode('tweet_words', ' OR ');
        //ユーザー名・名前（@i -@i）、リツイートは検索対象から除外するオプションを追加
        $query = $keywords . ' OR @i -@i' . ' exclude:retweets';

        // ツイート検索オプションのパラメーターを生成する
        $params = array(
            'q' => $query,
            'count' => 100, // リクエスト1回あたりの検索ツイート数（上限は100件）
            'lang' => 'ja',
            'locale' => 'ja',
            'result_type' => 'mixed', // recent 最新ツイート, popular 人気のツイート, mixed 全てのツイート
        );
        // 検索開始日時を指定する場合
        if ($since_at) {
            // TwitterAPIの日付形式に変換して検索オプションの配列に追加
            $params['since'] = $since_at->format('Y-m-d_H:i:s') . "_JST";
        }
        // 検索終了日時を指定する場合
        if ($until_at) {
            // TwitterAPIの日付形式に変換して検索オプションの配列に追加
            $params['until'] = $until_at->format('Y-m-d_H:i:s') . "_JST";
        }
        return $params;
    }


    /**
     * TwitterAPIでツイートを検索し、DB保存メソッドで保存するメソッド
     */
    public function requestAndSaveTweets($remain_count, $params)
    {
        $total_count = 0; //ツイートの合計保存件数
        $max_id = '';

        // 最大リクエスト回数までループしてツイートを検索
        for ($req_count = 1; $req_count < $remain_count; $req_count++) {
            dump(($req_count) . "回目");
            logger()->info(($req_count) . "回目");

            try {
                // ツイートをTwitterAPIで検索し、返却された検索結果を変数に格納
                $tweets_obj = Twitter::get("search/tweets", $params);
            } catch (TwitterOAuthException $e) {
                dump("エラーが発生しました");
                logger()->info("エラーが発生しました");
                break;
            }

            // TwitterAPIからのレスポンスが500エラーの場合は処理を終了
            if (http_response_code() === 500) {
                logger()->info("接続がタイムアウトしました");
                break;
            }

            // 検索結果がある場合
            if (property_exists($tweets_obj, 'statuses')) {
                // DBにレコードを保存するメソッドを実行
                $records_count = $this->createRecord($tweets_obj->statuses);
                dump($records_count . "件のツイートを保存しました");
                logger()->info($records_count . "件のツイートを保存しました");
                $total_count += $records_count;

                // レスポンスのメタデータから次の検索開始IDを取得
                $max_id = $this->fetchMaxId($tweets_obj->search_metadata);

                // APIから返却されたメタデータに次の検索開始ID（$max_id）がある場合
                if ($max_id) {
                    $next_id = $max_id - 1; //今回の取得対象にmax_idも含まれているため、次回はそのIDより1小さいIDからスタート
                    $params['max_id'] = $next_id;
                    logger()->info("次の開始位置：" . $next_id);
                    continue;
                } else {
                    // メタデータに次の検索開始ID（$max_id）がない場合はループを抜ける
                    logger()->info("最後まで取得しました" . $max_id);
                    break;
                }
            } else {
                // 検索結果が存在しない場合はループを抜ける
                logger()->info("検索結果は0件でした");
                $max_id = '';
                break;
            }
        }
        return [$total_count, $max_id, $req_count];
    }

    /**
     * 検索結果からツイート文章を取り出し、DBに保存するメソッド
     */
    public function createRecord($statuses)
    {
        $tweets = [];
        // 検索結果がある場合
        if ($statuses) {
            // 取得したツイート件数分ループを回し、必要なデータを配列に格納
            foreach ($statuses as $status) {
                $tweets[] = [
                    'tweet_id' => $status->id,  //ツイートID
                    'tweet_text' => $status->text, //ツイート本文
                    'tweeted_at' => date('Y-m-d H:i:s', strtotime($status->created_at)), //ツイート日時
                    'twitter_user_id' => $status->user->id,
                    'twitter_user_name' => $status->user->name,
                    'screen_name' => $status->user->screen_name,
                    'description' => $status->user->description,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            // tweetsテーブルに保存
            Tweet::insert($tweets);
        }
        // 保存した件数を返却
        return count($tweets);
    }

    /**
     * 検索結果のメタデータを引数で受け取り、
     * クエリ文字列next_resultsの中のmax_id（今回の検索終了TwitterID）を取得するメソッド
     */
    public function fetchMaxId($metadata)
    {
        // TwitterAPIから返却されたメタデータにrefresh_urlがある場合
        if (property_exists($metadata, 'refresh_url')) {
            // refresh_urlのクエリ文字列からsince_idの値（今回の検索開始TwitterID）のみ抜き出してログに出力
            $refresh_url = $metadata->refresh_url;
            $since_id = preg_replace('/.*?since_id=([\d]+)&.*/', '$1', $refresh_url);
            logger()->info("since_id:{$since_id}");
        }
        // TwitterAPIから返却されたメタデータにnext_resultsがある場合
        if (property_exists($metadata, 'next_results')) {
            // next_resultsのクエリ文字列からmax_idの値（今回の検索終了TwitterID）のみ抜き出し
            $next_results = $metadata->next_results;
            $max_id = preg_replace('/.*?max_id=([\d]+)&.*/', '$1', $next_results);
            dump("max_id:" . $max_id);
            logger()->info("max_id:{$max_id}");
            return $max_id; //max_idを返却
        }
        // next_results がない場合は処理を終了
        dump("next_resultsはありませんでした");
        logger()->info("next_resultsはありませんでした");
        return;
    }
}
