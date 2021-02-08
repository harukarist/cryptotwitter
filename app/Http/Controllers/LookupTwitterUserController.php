<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;

class LookupTwitterUserController extends Controller
{
    public $user_id = ''; //追加したいユーザーID（複数の場合はカンマ区切り）
    public $screen_name = ''; //追加したいユーザーのスクリーンネーム（複数の場合はカンマ区切り）

    public function addUsers()
    {
        $this->screen_name = '';

        // 検索パラメータを生成
        $params = $this->getParams();

        // // TwitterAPIでツイートを検索し、該当データを保存
        $this->requestUsers($params);
        return;
    }

    // 検索用パラメーターを生成
    public function getParams()
    {
        // ツイート検索オプションを指定
        $params = array(
            'user_id' => $this->user_id,
            'screen_name' => $this->screen_name,
            'include_entities' => true, //ツイートオブジェクト内のentitiesプロパティを含めるか否か
        );
        return $params;
    }

    // TwitterAPIでツイートを検索
    public function requestUsers($params)
    {
        $create_total = 0;
        $update_total = 0;

        // ツイートをTwitterAPIで検索し、返却された検索結果を変数に格納
        $users_arr = \Twitter::get("users/lookup", $params);

        if (http_response_code() == 500) {
            echo "接続がタイムアウトしました<br>";
            logger()->info("接続がタイムアウトしました");
            return;
        }

        // ページが存在しない場合はobject、ページが存在する場合はarray（子階層はobject)で返ってくるため、
        // 親階層がobjectで返ってきた場合はjsonにエンコードした後、配列形式にデコードする。
        if (is_object($users_arr)) {
            $users_arr = json_decode(json_encode($users_arr), true);
        }

        // APIから返ってきたオブジェクトにエラープロパティがあれば処理を終了
        if (array_key_exists('errors', $users_arr)) {
            echo 'ユーザー情報は存在しません' . '<br>';
            logger()->info("ユーザー情報は存在しません");
            return;
        }

        // 検索結果がある場合
        $users_count = count($users_arr);
        if ($users_count) {
            echo "ユーザー情報を" . $users_count . "件取得<br>";
            logger()->info("ユーザー情報を" . $users_count . "件取得");

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
            return;
        }

        // ユーザー取得ログをDBに保存
        DB::table('fetch_users_logs')->insert([
            'create_total' => $create_total,
            'update_total' => $update_total,
            'request_count' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return;
    }

    // 検索結果からユーザー情報を取り出し、DBに保存
    public function createRecord($users_arr)
    {
        $create_count = 0;
        $update_count = 0;
        $query = [];
        // 検索結果がある場合
        if ($users_arr) {
            // 取得したツイート件数分ループを回し、必要なデータを配列に格納
            foreach ($users_arr as $user) {
                echo $user->name . '<br>';
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
                        'tweet_id' => $user->status->id, //最新ツイートID
                        'tweet_text' => $user->status->text, //最新ツイート文章
                        'tweeted_at' => date('Y-m-d H:i:s', strtotime($user->status->created_at)), //最新ツイート日時
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
