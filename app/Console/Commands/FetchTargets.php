<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Facades\Twitter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuthException;

/**
 * TwitterAPIで仮想通貨関連のキーワードを含むTwitterアカウントを取得して
 * DBに保存するコマンド
 */
class FetchTargets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:targets';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'TwitterAPIから仮想通貨アカウントを取得し、DBに保存';

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
        // ユーザー検索の最大リクエスト回数の初期値（上限は15分間に900回）
        $MAX_REQUEST = 900;

        // 仮想通貨アカウントの検索キーワード
        $KEYWORDS = ['仮想通貨', '暗号資産', 'ビットコイン'];

        //ログファイルに書き込む
        logger()->info('>>>> Twitterユーザー保存バッチを実行します');

        foreach ($KEYWORDS as $keyword) {
            // TwitterAPIのリクエスト残り回数を取得
            $limit_count = $this->checkLimit();

            // リクエスト残り回数が0以下の場合は処理を終了
            if ($limit_count <= 0) {
                dump("Twitterアカウント取得のリクエスト上限に達しました");
                logger()->info("Twitterアカウント取得のリクエスト上限に達しました");
                return;
            }
            // リクエスト残り回数が初期値より少なければ、リクエスト残り回数をリクエスト回数とする
            if ($limit_count < $MAX_REQUEST) {
                $remain_count = $limit_count;
            } else {
                // リクエスト残り回数が初期値より大きければ、初期値をリクエスト回数とする
                $remain_count = $MAX_REQUEST;
            }
            logger()->info("リクエスト回数はあと" . $remain_count . "回");

            // 検索パラメータを生成
            // Twitterアカウント検索オプションを指定
            $params = array(
                'q' => $keyword,
                'count' => 20, // 1ページ毎に取得するユーザー件数（上限は20件）
            );

            dump("「{$keyword}」でTwitterアカウントを検索");
            logger()->info("「{$keyword}」でTwitterアカウントを検索");

            // TwitterAPIでTwitterアカウントを検索し、該当データを保存
            $this->requestUsers($remain_count, $params);
        }
        logger()->info('Twitterユーザー保存バッチを実行しました <<<<');
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
            $limit_obj = $status->resources->users;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('/users/search', $limit_arr)) {
                if (property_exists($limit_arr['/users/search'], 'remaining')) {
                    $limit_count = $limit_arr['/users/search']->remaining; // 残り使用回数
                    logger()->info("残り:{$limit_count}回");
                    return $limit_count;
                }
            }
        }

        return 0;
    }

    /**
     * TwitterAPIでキーワードを含むTwitterアカウントを検索するメソッド
     */
    public function requestUsers($remain_count, $params)
    {
        $create_total = 0;
        $update_total = 0;
        $page_num = 1;

        // 最大リクエスト回数までループ
        for ($req_count = 1; $req_count <= $remain_count; $req_count++) {
            $params['page'] = $page_num;

            try {
                // TwitterアカウントをTwitterAPIで検索し、返却された検索結果を変数に格納
                $users_arr = Twitter::get("users/search", $params);
            } catch (TwitterOAuthException $e) {
                dump("エラーが発生しました");
                logger()->info("エラーが発生しました");
                break;
            }

            if (http_response_code() == 500) {
                dump("接続がタイムアウトしました");
                logger()->info("接続がタイムアウトしました");
                break;
            }

            // ページが存在しない場合はobject、ページが存在する場合はarray（子階層はobject)で返ってくるため、
            // 親階層がobjectで返ってきた場合はjsonにエンコードした後、配列形式にデコードする。
            if (is_object($users_arr)) {
                $users_arr = json_decode(json_encode($users_arr), true);
            }

            // APIから返ってきたオブジェクトにエラープロパティがあれば処理を終了
            if (array_key_exists('errors', $users_arr)) {
                logger()->info("{$params['page']}ページ目にユーザー情報は存在しません");
                break;
            }

            // 検索結果がある場合
            $users_count = count($users_arr);
            if ($users_count) {
                // 検索結果から必要なデータを抽出し、DBに保存
                $count = $this->createRecord($users_arr);
                // 新規保存したレコード件数、更新したレコード件数を加算
                $create_total += $count['create'];
                $update_total += $count['update'];
            } else {
                dump("検索結果は0件でした");
                logger()->info("検索結果は0件でした");
                break;
            }
            // 次のページにループを回す
            $page_num++;
        }

        dump("{$create_total}件のユーザー情報を新規保存しました");
        dump("{$update_total}件のユーザー情報を更新しました");
        logger()->info("{$create_total}件のユーザー情報を新規保存しました");
        logger()->info("{$update_total}件のユーザー情報を更新しました");
        logger()->info("{$req_count}回リクエスト済み");

        // ユーザー取得ログをDBに保存
        DB::table('fetch_targets_logs')->insert([
            'create_total' => $create_total,
            'update_total' => $update_total,
            'request_count' => $req_count,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return;
    }

    /**
     * 検索結果からTwitterアカウント情報を取り出し、DBに保存するメソッド
     */
    public function createRecord($users_arr)
    {
        $create_count = 0;
        $update_count = 0;
        $query = [];
        // 検索結果がある場合
        if ($users_arr) {
            // 取得したTwitterアカウント件数分ループを回し、必要なデータを配列に格納
            foreach ($users_arr as $user) {
                $query = [
                    'twitter_id' => $user->id,  //ユーザーID
                    'user_name' => $user->name, //ユーザー名
                    'screen_name' => $user->screen_name, //@から始まるアカウント名
                    'follow_num' => $user->friends_count, //フォロー数
                    'follower_num' => $user->followers_count, //フォロワー数
                    'profile_text' => $user->description, //プロフィール文章
                    'profile_img' => $user->profile_image_url_https, //プロフィールアイコンURL
                    'url' => $user->url, //URL
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                // statusプロパティがある場合は対象ユーザーの最新ツイートもDBに保存
                if (property_exists($user, 'status')) {
                    $query = array_merge($query, [
                        'tweet_id' => $user->status->id, //最新ツイートのID
                        'tweet_text' => $user->status->text, //最新ツイートの文章
                        'tweeted_at' => date('Y-m-d H:i:s', strtotime($user->status->created_at)), //最新ツイートの日時
                    ]);
                }

                // 該当のTwitterIDを持つレコードをテーブルから1件取得
                $target = DB::table('target_users')->where('twitter_id', $user->id)->first();
                if (!$target) {
                    // レコードがなければ新規作成
                    DB::table('target_users')->insert($query);
                    $create_count++;
                } else {
                    // レコードがあれば更新
                    DB::table('target_users')->where('twitter_id', $user->id)->update($query);
                    $update_count++;
                }
            }
            // 新規保存したレコード件数、更新したレコード件数を返却
            $count['create'] = $create_count; // 新規保存したレコード件数
            $count['update'] = $update_count; // 更新したレコード件数
            return $count;
        }
        return;
    }
}
