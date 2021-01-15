<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;

// フォロー対象となるTwitterの仮想通貨アカウントを取得
class FetchTwitterUserController extends Controller
{
    public $KEYWORDS = '仮想通貨 暗号資産'; //ユーザー検索キーワード

    public function fetchUsers()
    {
        $MAX_REQUEST = 100; // ユーザー検索の最大リクエスト回数の初期値（上限は15分間に900回）
        // TwitterAPIのリクエスト残り回数を取得
        $remain_count = $this->checkLimit($MAX_REQUEST);
        // リクエスト残り回数が0の場合は処理を終了
        if (!$remain_count) {
            echo "Twitterアカウント取得のリクエスト上限に達しました";
            logger()->info("Twitterアカウント取得のリクエスト上限に達しました");
            return;
        }

        // 検索パラメータを生成
        $params = $this->getParams();

        // // TwitterAPIでTwitterアカウントを検索し、該当データを保存
        $this->requestUsers($remain_count, $params);
        return;
    }

    // 検索用パラメーターを生成
    public function getParams()
    {
        // Twitterアカウント検索オプションを指定
        $params = array(
            'q' => $this->KEYWORDS,
            'count' => 20, // 1ページ毎に取得するユーザー件数（上限は20件）
        );
        return $params;
    }

    // TwitterAPIでレートリミットを取得
    public function checkLimit($max_request)
    {
        //残り使用可能回数をTwitterAPIでチェック
        $status = \Twitter::get("application/rate_limit_status");

        // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
        if (property_exists($status, 'errors')) {
            $max_request = 0;
            echo "残り使用可能回数が取得できませんでした<br>";
            logger()->info("残り使用可能回数が取得できませんでした");
            return $max_request;
        }

        // 検索APIの残り使用可能回数が存在する場合は回数の値を取得
        if (property_exists($status, 'resources')) {
            $limit_obj = $status->resources->users;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('/users/search', $limit_arr)) {
                if (property_exists($limit_arr['/users/search'], 'remaining')) {
                    $remain_count = $limit_arr['/users/search']->remaining; // 残り使用回数
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

    // TwitterAPIでTwitterアカウントを検索
    public function requestUsers($remain_count, $params)
    {
        $create_total = 0;
        $update_total = 0;
        $page_num = 1;

        // 最大リクエスト回数までループ
        for ($req_count = 1; $req_count <= $remain_count; $req_count++) {
            $params['page'] = $page_num;
            // echo $req_count . '回目/' . $remain_count . '回<br>';
            // logger()->info(($req_count) . "回目");
            // echo $params['page'] . 'ページ目を取得' . '<br>';
            // logger()->info($params['page'] . "ページ目を取得");

            // TwitterアカウントをTwitterAPIで検索し、返却された検索結果を変数に格納
            $users_arr = \Twitter::get("users/search", $params);

            if (http_response_code() == 500) {
                echo "接続がタイムアウトしました<br>";
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
                echo $params['page'] . 'ページ目にユーザー情報は存在しません' . '<br>';
                logger()->info($params['page'] . "ページ目にユーザー情報は存在しません");
                break;
            }

            // 検索結果がある場合
            $users_count = count($users_arr);
            if ($users_count) {
                // echo "ユーザー情報を" . $users_count . "件取得<br>";
                // logger()->info("ユーザー情報を" . $users_count . "件取得");

                // 検索結果から必要なデータを抽出し、DBに保存
                $count = $this->createRecord($users_arr);

                echo $count['create'] . "件のユーザー情報を保存しました<br>";
                logger()->info($count['create'] . "件のユーザー情報を保存しました");
                $create_total += $count['create'];

                echo $count['update'] . "件のユーザー情報を更新しました<br>";
                logger()->info($count['update'] . "件のユーザー情報を更新しました");
                $update_total += $count['update'];
            } else {
                echo "検索結果は0件でした<br>";
                logger()->info("検索結果は0件でした");
                break;
            }
            $page_num++;
        }

        echo  $create_total . "件のユーザー情報を新規保存しました<br>";
        logger()->info($create_total . "件のユーザー情報を新規保存しました");
        echo  $update_total . "件のユーザー情報を更新しました<br>";
        logger()->info($update_total . "件のユーザー情報を更新しました");

        echo  $req_count . "回リクエスト済み<br>";
        logger()->info($req_count . "回リクエスト済み");

        // ユーザー取得ログをDBに保存
        DB::table('fetch_users_logs')->insert([
            'create_total' => $create_total,
            'update_total' => $update_total,
            'request_count' => $req_count,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return;
    }

    // 検索結果からTwitterアカウント情報を取り出し、DBに保存
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
                // // target_usersテーブルに存在する場合は更新、存在しない場合は保存
                // $target = TargetUser::updateOrCreate([
                //     'twitter_id' => $user->id
                // ], $query);
            }
            $count['create'] = $create_count;
            $count['update'] = $update_count;
            return $count;
        }
        return;
    }
}
