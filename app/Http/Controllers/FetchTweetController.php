<?php

namespace App\Http\Controllers;

use App\Trend;
use App\Tweet;
use Carbon\Carbon;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;

class FetchTweetController extends Controller
{
    // 現在日時から保存済み最新ツイートIDまでの範囲を検索して保存
    public function fetchRecentTweets()
    {
        $dt = Carbon::today();
        $since = $dt->copy()->format('Y-m-d');
        $until = $dt->copy()->addDay()->format('Y-m-d');
        // 検索パラメータを取得
        $params = $this->getParams($since, $until);
        // var_dump($params);

        // 対象日のツイートが保存されていればDBから取得済みTweetIDを取得
        $last = Tweet::select('tweet_id')->whereDate('tweeted_at', $since)->orderBy('tweet_id', 'DESC')->first();

        // 対象日の保存済みツイートがあれば、そのTweetIDより新しいツイートを取得するパラメータを追加
        if ($last && $last->tweet_id) {
            $params['since_id'] = $last->tweet_id;
            echo  $last->tweet_id . "より新しいツイートを取得<br>";
            logger()->info($last->tweet_id . "より新しいツイートを取得");
        } else {
            echo  $since . "の保存データはまだありません<br>";
            logger()->info($since . "の保存データはまだありません");
        }

        $max_request = 180; // ツイート検索の最大リクエスト回数の初期値（上限は15分間に180回）
        // TwitterAPIのリクエスト残り回数を取得
        $remain_count = $this->checkLimit($max_request);
        // リクエスト残り回数が0の場合は処理を終了
        if (!$remain_count) {
            echo "一括ツイート検索のリクエスト上限に達しました";
            logger()->info("一括ツイート検索のリクエスト上限に達しました");
            return;
        }
        // TwitterAPIで検索、保存
        $this->requestTweets($remain_count, $params);

        return;
    }

    // 今日の0:00までの１週間分のツイートを検索し、未取得の時間帯があればDBに保存する処理
    public function fetchWeeklyTweets()
    {
        $MAX_REQUEST = 180; // ツイート検索の最大リクエスト回数の初期値（上限は15分間に180回）
        // TwitterAPIのリクエスト残り回数を取得
        $remain_count = $this->checkLimit($MAX_REQUEST);
        // リクエスト残り回数が0の場合は処理を終了
        if (!$remain_count) {
            echo "一括ツイート検索のリクエスト上限に達しました";
            logger()->info("一括ツイート検索のリクエスト上限に達しました");
            return;
        }
        // 今日の0:00の日時を取得
        $dt = Carbon::today();
        $carbon_now = Carbon::now();
        // 7日前から1日ずつ処理
        $target_date = $dt->subDays(7);

        // 0時から1時間毎に未取得のツイートがないかをチェックし、あれば取得、なければ次の時間帯へループする。
        for ($target_hour = 0; true; $target_hour++) {
            // 検索対象とする日時を生成
            $since_at = $target_date->copy()->addHours($target_hour);
            $until_at = $target_date->copy()->addHours($target_hour + 1);

            // 取得対象の開始日時と現在時刻との差が2時間未満の場合は取得しない
            if ($since_at->diffInHours($carbon_now) < 2) {
                break;
            }

            // 対象の開始日時の最新ログデータがあれば、該当ログのnext_idを取得
            $log = DB::table('fetch_tweets_logs')->select('next_id')
                ->where('since_at', '=', $since_at)
                ->orderBy('id', 'DESC')->first();
            // 対象の開始日時のログデータがあり、取得予定IDが空の場合は全て取得済みのため次の時間帯へ
            if ($log && !($log->next_id)) {
                echo  $since_at . "〜" . $until_at .  "のツイートは全て取得済み<br>";
                echo  $carbon_now . "<br>";
                logger()->info($since_at . "〜" . $until_at . "のツイートは全て取得済み");
                continue; //以降の処理は行わずに次のループへ
            }

            // TwitterAPIの日付形式に変換
            $since_param = $since_at->format('Y-m-d_H:i:s') . "_JST";
            $until_param = $until_at->format('Y-m-d_H:i:s') . "_JST";
            echo  $since_at . "〜" . $until_at . "までの期間を検索<br>";

            // 検索パラメータを生成
            $params = $this->getParams($since_param, $until_param);

            // 対象の時間帯にツイートが保存されていればDBから一番若い取得済みTweetIDを取得
            $first = Tweet::select('tweet_id', 'tweeted_at')
                ->where('tweeted_at', '>=', $since_at)
                ->where('tweeted_at', '<', $until_at)
                ->orderBy('tweet_id', 'ASC')->first();

            $first_id = '';
            // 取得済みTweetIDをmax_idに設定
            if ($first && $first->tweet_id) {
                $first_id = $first->tweet_id;
                $params['max_id'] = $first_id - 1; //取得済みIDよりも1つ小さいIDのツイートから検索開始
                echo  $first->tweet_id . "より古いツイートをチェック";
                echo  'ツイート日時 ' . $first->tweeted_at . "<br>";
                logger()->info($first->tweet_id . "より古いツイートをチェック");
                logger()->info('ツイート日時 ' . $first->tweeted_at);
            } else {
                echo  $since_param . "〜" . $until_param . "の保存データはまだありません<br>";
                logger()->info($since_param . "〜" . $until_param . "の保存データはまだありません");
            }
            // TwitterAPIでツイートを検索し、該当データを保存
            [$total_count, $max_id, $req_count] = $this->requestTweets($remain_count, $params);
            echo  $req_count . "回リクエスト済み<br>";
            logger()->info($req_count . "回リクエスト済み");

            // 対象時間帯のログをDBに保存して次の時間帯のチェックへ
            DB::table('fetch_tweets_logs')->insert([
                'since_at' => $since_at,
                'until_at' => $until_at,
                'total_count' => $total_count,
                'begin_id' => $first_id,
                'next_id' => $max_id,
                'created_at' => Carbon::now(),
            ]);

            // 残り使用可能回数から今回のリクエスト回数を減らす
            $remain_count -= $req_count;

            echo  $remain_count . "回可能<br>";
            logger()->info($remain_count . "回可能");

            // 残り使用可能回数が0以下なら処理を終了
            if ($remain_count <= 0) {
                return;
            }
        }
        return;
    }


    // 検索用パラメーターを生成
    public function getParams($since, $until)
    {
        $tweet_count = 100; // 1回あたりの検索ツイート数（上限は100件）

        // 検索キーワード用の通貨名、日本語名をTrendモデルから取得し、implode()で文字列に変換
        // $collection = Trend::select('currency_name', 'currency_ja')->get();
        // $keywords = $collection->implode('currency_name', ' OR ');
        // $keywords .= ' OR ';
        // $keywords .= $collection->implode('currency_ja', ' OR ');

        $words_arr = Trend::select('tweet_words')->get();
        $keywords = $words_arr->implode('tweet_words', ' OR ');

        // ツイート検索オプションを指定
        $params = array(
            'q' => $keywords . ' OR @i -@i' . ' exclude:retweets', //アカウント名とリツイートは検索対象から除く
            'count' => $tweet_count,
            'lang' => 'ja',
            'locale' => 'ja',
            'result_type' => 'mixed', // recent 最新ツイート, popular 人気のツイート, mixed 全てのツイート

        );
        // dd($keywords, $params);

        if ($since) {
            $params['since'] = $since;
            echo "開始日" . $since . "から<br>";
            logger()->info("開始日:{$since}から");
        }
        if ($until) {
            $params['until'] = $until;
            echo "終了日" . $until . "まで<br>";
            logger()->info("終了日:{$until}まで");
        }
        return $params;
    }

    // TwitterAPIでレートリミットを取得
    public function checkLimit($max_request)
    {
        //残り使用可能回数をTwitterAPIでチェック
        $status = \Twitter::get("application/rate_limit_status");
        // dd($status);

        // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
        if (property_exists($status, 'errors')) {
            $max_request = 0;
            echo "残り使用可能回数が取得できませんでした<br>";
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
                    echo "残り" . $remain_count . "回<br>";
                    logger()->info("残り:{$remain_count}回");

                    // 残り回数が初期値より少なければ、残り回数を最大リクエスト回数とする
                    if ($remain_count < $max_request) {
                        return $remain_count;
                    }
                }
            }
        }

        return $max_request;
    }

    // TwitterAPIでツイートを検索
    public function requestTweets($remain_count, $params)
    {
        $total_count = 0;
        $max_id = '';
        // 最大リクエスト回数までループ
        for ($req_count = 1; $req_count <= $remain_count; $req_count++) {
            echo $req_count . '回目/' . $remain_count . '回<br>';
            logger()->info(($req_count) . "回目");

            // ツイートをTwitterAPIで検索し、返却された検索結果を変数に格納
            $tweets_obj = \Twitter::get("search/tweets", $params);
            // dd($tweets_obj);

            if (http_response_code() === 500) {
                echo "接続がタイムアウトしました<br>";
                logger()->info("接続がタイムアウトしました");
                break;
            }

            // 検索結果がある場合
            if (property_exists($tweets_obj, 'statuses')) {
                // 検索結果から必要なデータを抽出し、DBに保存
                $records_count = $this->createRecord($tweets_obj->statuses);
                echo $records_count . "件のツイートを保存しました<br>";
                logger()->info($records_count . "件のツイートを保存しました");
                $total_count += $records_count;

                // レスポンスのメタデータから次の検索開始IDを取得
                $max_id = $this->createNextParam($tweets_obj->search_metadata);
                // 次のループ時の検索パラメーターにmax_idを追加
                if ($max_id) {
                    $next_id = $max_id - 1; //今回の取得対象にmax_idも含まれているため、次回はそのID未満からスタート
                    $params['max_id'] = $next_id;
                    echo "次の開始位置：" . $next_id . "<br>";
                    logger()->info("次の開始位置：" . $next_id);
                    continue;
                } else {
                    echo "最後まで取得しました" . $max_id . "<br>";
                    logger()->info("最後まで取得しました" . $max_id);
                    break;
                }
            } else {
                echo "検索結果は0件でした<br>";
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
            // dd($tweets);
            // Tweetsテーブルに保存
            Tweet::insert($tweets);
        }
        return count($tweets);
    }

    // 検索結果からツイート文章を取り出し、DBに保存
    public function createNextParam($metadata)
    {
        if (property_exists($metadata, 'refresh_url')) {
            $refresh_url = $metadata->refresh_url;
            // $refresh_urlのクエリ文字列からmax_idの値のみ抜き出し
            $since_id = preg_replace('/.*?since_id=([\d]+)&.*/', '$1', $refresh_url);
            echo "since_id:" . $since_id . "<br>";
            logger()->info("since_id:{$since_id}");
        }
        if (property_exists($metadata, 'next_results')) {
            $next_results = $metadata->next_results;
            // 次の検索対象がある場合は、$next_resultsのクエリ文字列からmax_idの値のみ抜き出し
            $max_id = preg_replace('/.*?max_id=([\d]+)&.*/', '$1', $next_results);
            echo "max_id:" . $max_id . "<br>";
            logger()->info("max_id:{$max_id}");
            return $max_id;
        }
        // next_results がない場合は処理を終了
        echo "next_resultsはありませんでした<br>";
        logger()->info("next_resultsはありませんでした");
        return;
    }

    // public function getTweet()
    // {
    //     date_default_timezone_set('Asia/Tokyo');
    //     $until = date("Y-m-d_H:i:s") . "_JST";
    //     $since = date('Y-m-d_H:i:s', strtotime('-7 day', time())) . "_JST";

    //     // 通貨名と日本語名のコレクションをTrendモデルから取得
    //     $collection = Trend::select('currency_name', 'currency_ja')->get();

    //     // implode()でコレクションを'OR'でつなげた文字列に変換して検索キーワードを作成
    //     $keywords = $collection->implode('currency_name', ' OR ');
    //     $keywords .= ' OR ';
    //     $keywords .= $collection->implode('currency_ja', ' OR ');
    //     // dd($keywords);

    //     $tweet_count = 100; // 1回あたりの検索ツイート数（上限は100件）
    //     $max_request = 180; // ツイート検索の最大リクエスト回数（上限は15分間に180回）

    //     //残り使用回数をチェック
    //     $remain_count = $this->checkLimit();
    //     echo "残り" . $remain_count . "回<br>";
    //     logger()->info("残り:{$remain_count}回");

    //     if ($remain_count < $max_request) {
    //         $max_request = $remain_count;
    //     }
    //     // dd($max_request);

    //     // ツイート検索オプションを指定
    //     $params = array(
    //         'q' => $keywords,
    //         'count' => $tweet_count,
    //         'lang' => 'ja',
    //         'locale' => 'ja',
    //         'result_type' => 'mixed', // recent 最新ツイート, popular 人気のツイート, mixed 全てのツイート
    //         'since' => $since,
    //         'until' => $until,
    //     );

    //     //このTweetIDより古いツイートを取得
    //     $max_id = DB::table('tweets')->orderBy('tweet_id', 'ASC')->first()->tweet_id;
    //     if ($max_id) {
    //         $params['max_id'] = $max_id;
    //     }
    //     // ツイートを検索してDBに保存
    //     $this->requestTweets($max_request, $params);


    //     //残り使用回数をチェック
    //     $remain_count = $this->checkLimit();
    //     echo "残り" . $remain_count . "回<br>";
    //     logger()->info("残り:{$remain_count}回");

    //     // 残り使用回数があれば最新ツイートを取得
    //     if ($remain_count) {
    //         $since_id = DB::table('tweets')->orderBy('tweet_id', 'DESC')->first()->tweet_id; //このTweetIDより新しいツイートを取得
    //         if ($since_id) {
    //             $params['since_id'] = $since_id;
    //             $params['max_id'] = '';
    //         }
    //         // dd($params);
    //         // ツイートを検索してDBに保存
    //         $this->requestTweets($remain_count, $params);
    //     }
    //     return;
    // }
}
