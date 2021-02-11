<?php

namespace App\Http\Controllers;

use App\Trend;
use App\Tweet;
use Carbon\Carbon;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;

// TwitterAPIでキーワードを含むツイートを取得してテーブルに保存する
// TwitterAPIでは、指定した期間の中でツイートIDが大きいものから順に返却される
class FetchTweetController extends Controller
{
    // 最後のツイート取得ログ以降のツイートを10分刻みで取得
    public function fetchLatestTweets()
    {
        $max_requests = 30; // ツイート検索の最大リクエスト回数の初期値（上限は15分間に180回）

        // TwitterAPIのリクエスト残り回数を取得
        $remain_count = $this->checkLimit($max_requests);

        // リクエスト残り回数が0の場合は処理を終了
        if (!$remain_count) {
            logger()->info("一括ツイート検索のリクエスト上限に達しました");
            return;
        }

        // 残り使用可能回数が0になる、または10分前のツイートを取得できるまでループ
        for ($i = 0; true; $i++) {
            // 最新日時のツイート取得ログデータ1件をDBから取得
            $log = DB::table('fetch_tweets_logs')->orderBy('until_at', 'DESC')->first();
            // ログデータがない場合は以降の処理は行わず、1週間前からツイートを取得するメソッドを実行
            if (!$log) {
                $this->fetchWeeklyTweets();
                return;
            }

            // ログデータがある場合
            // next_idカラムに値がある場合は、next_idをAPIのmax_id（取得開始id)に指定してその時間帯のツイートを取得
            if ($log->next_id) {
                $since_at = new Carbon($log->since_at);
                $until_at = new Carbon($log->until_at);
                $max_id = $log->next_id;
                // 検索パラメータを生成
                $params = $this->getParams($since_at, $until_at);
                $params['max_id'] = $max_id;
            } else {
                // next_idカラムが空の場合はuntil_atより後の10分間のツイートを取得する
                $since_at = new Carbon($log->until_at);
                $until_at = $since_at->copy()->addMinutes(10);
                // 現在日時を取得
                $carbon_now = Carbon::now();
                // 取得対象の開始日時と現在時刻の差が10分未満の場合は処理を終了
                if ($since_at->diffInMinutes($carbon_now) < 10) {
                    echo ("直近の未取得ツイートはありません<br>");
                    logger()->info("直近の未取得ツイートはありません");
                    break;
                }
                // 検索パラメータを生成
                $params = $this->getParams($since_at, $until_at);
            }

            echo ($since_at . "〜" . $until_at . "のツイートを取得<br>");
            logger()->info($since_at . "〜" . $until_at . "のツイートを取得");

            // TwitterAPIでツイートを検索し、該当データを保存
            [$total_count, $max_id, $req_count] = $this->requestAndSaveTweets($remain_count, $params);

            // 対象時間帯のログをDBに保存
            DB::table('fetch_tweets_logs')->insert([
                'since_at' => $since_at,
                'until_at' => $until_at,
                'total_count' => $total_count,
                'begin_id' => $params['max_id'] ?? '',
                'next_id' => $max_id,
                'created_at' => Carbon::now(),
            ]);

            echo ($req_count . "回リクエスト済み<br>");
            logger()->info($req_count . "回リクエスト済み");

            // 残り使用可能回数から今回のリクエスト回数を減らす
            $remain_count -= $req_count;
            echo ("あと" . $remain_count . "回リクエスト可能");
            logger()->info("あと" . $remain_count . "回リクエスト可能");

            // 残り使用可能回数が0以下なら処理を終了
            if ($remain_count <= 0) {
                return;
            }
        }
    }


    // 現在時刻から1時間前までの１週間分のツイートのうち、未取得の時間帯があればDBに保存する処理
    public function fetchWeeklyTweets()
    {
        $max_requests = 180; // ツイート検索の最大リクエスト回数の初期値（上限は15分間に180回）

        // TwitterAPIのリクエスト残り回数を取得
        $remain_count = $this->checkLimit($max_requests);

        // リクエスト残り回数が0の場合は処理を終了
        if (!$remain_count) {
            logger()->info("一括ツイート検索のリクエスト上限に達しました");
            return;
        }
        // 今日の0:00の日時を取得
        $dt = Carbon::today();
        // 現在日時を取得
        $carbon_now = Carbon::now();
        // 7日前から処理を開始
        $target_date = $dt->subDays(7);

        // 7日前の0時から1時間毎に未取得のツイートがないかをチェックし、あれば取得、なければ次の時間帯へループ
        for ($add_hours = 0; true; $add_hours++) {
            // 検索対象とする日時を生成
            $since_at = $target_date->copy()->addHours($add_hours);
            $until_at = $target_date->copy()->addHours($add_hours + 1);

            // 取得対象の開始日時と現在時刻との差が1時間未満の場合は取得しない
            if ($since_at->diffInHours($carbon_now) < 1) {
                break;
            }

            // 対象の開始日時の最新ログデータがあれば、該当ログのnext_idを取得
            $log = DB::table('fetch_tweets_logs')->select('next_id')
                ->where('since_at', '=', $since_at)
                ->orderBy('id', 'DESC')->first();
            // 対象の開始日時のログデータがあり、next_id（次回の取得予定ID）が空の場合は全て取得済みのため次の時間帯へ
            if ($log && !($log->next_id)) {
                logger()->info($since_at . "〜" . $until_at . "のツイートは全て取得済み");
                continue; //以降の処理は行わずに次のループへ
            }

            // 検索パラメータを生成
            $params = $this->getParams($since_at, $until_at);

            // next_id（次回の取得予定ID）がある場合は検索パラメータのmax_idに設定
            if ($log->next_id) {
                $params['max_id'] = $log->next_id;
                logger()->info($log->next_id . "より古いツイートをチェック");
            } else {
                // 取得済みTweetIDがない場合は検索開始IDを指定しない
                logger()->info($since_at . "〜" . $until_at . "の保存データはまだありません");
            }

            // TwitterAPIでツイートを検索し、該当データを保存
            [$total_count, $max_id, $req_count] = $this->requestAndSaveTweets($remain_count, $params);

            // 対象時間帯のログをDBに保存して次の時間帯のチェックへ
            DB::table('fetch_tweets_logs')->insert([
                'since_at' => $since_at,
                'until_at' => $until_at,
                'total_count' => $total_count,
                'begin_id' => $params['max_id'] ?? '',
                'next_id' => $max_id,
                'created_at' => Carbon::now(),
            ]);

            // 残り使用可能回数から今回のリクエスト回数を減らす
            logger()->info($req_count . "回リクエスト済み");
            $remain_count -= $req_count;

            logger()->info("あと" . $remain_count . "回リクエスト可能");

            // 残り使用可能回数が0以下なら処理を終了
            if ($remain_count <= 0) {
                return;
            }
        }
        return;
    }

    // TwitterAPIでレートリミットを取得
    public function checkLimit($max_request)
    {
        //残り使用可能回数をTwitterAPIでチェック
        $status = \Twitter::get("application/rate_limit_status");

        // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
        if (property_exists($status, 'errors')) {
            $max_request = 0;
            logger()->info("残り使用可能回数が取得できませんでした");
            return $max_request;
        }

        // 検索APIの残り使用可能回数が存在する場合は回数の値を取得
        if (property_exists($status, 'resources')) {
            $limit_obj = $status->resources->search;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('/search/tweets', $limit_arr)) {
                if (property_exists($limit_arr['/search/tweets'], 'remaining')) {
                    $remain_count = $limit_arr['/search/tweets']->remaining; // 残り使用回数
                    logger()->info("残り:{$remain_count}回");

                    // 残り回数が初期値より少なければ、残り回数を最大リクエスト回数とする
                    if ($remain_count < $max_request) {
                        return $remain_count;
                    }
                }
            }
        }
        // その他の場合は当初の最大リクエスト回数とする
        return $max_request;
    }


    // 検索用パラメーターを生成
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


    // TwitterAPIでツイートを検索
    public function requestAndSaveTweets($remain_count, $params)
    {
        $total_count = 0; //ツイートの合計保存件数
        $max_id = '';

        // 最大リクエスト回数までループしてツイートを検索
        for ($req_count = 1; $req_count <= $remain_count; $req_count++) {
            logger()->info(($req_count) . "回目");

            // ツイートをTwitterAPIで検索し、返却された検索結果を変数に格納
            $tweets_obj = \Twitter::get("search/tweets", $params);

            // TwitterAPIからのレスポンスが500エラーの場合は処理を終了
            if (http_response_code() === 500) {
                logger()->info("接続がタイムアウトしました");
                break;
            }

            // 検索結果がある場合
            if (property_exists($tweets_obj, 'statuses')) {
                // DBにレコードを保存
                $records_count = $this->createRecord($tweets_obj->statuses);
                echo $records_count . "件のツイートを保存しました<br>";
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
    // 検索結果からツイート文章を取り出し、DBに保存
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
            // Tweetsテーブルに保存
            Tweet::insert($tweets);
        }
        return count($tweets);
    }

    // 検索結果のメタデータからmax_idを取得
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
            echo "max_id:" . $max_id . "<br>";
            logger()->info("max_id:{$max_id}");
            return $max_id; //max_idを返却
        }
        // next_results がない場合は処理を終了
        logger()->info("next_resultsはありませんでした");
        return;
    }
}
