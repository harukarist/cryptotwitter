<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\FetchTwitterUserController;

class FetchTwproController extends FetchTwitterUserController
{
    protected $remain_count = 0;
    protected $until_at = '';
    protected $params = [];

    public function fetchUsers()
    {
        // ユーザー検索オプションを指定
        $this->params = [
            'q' => $this->KEYWORDS, //親コントローラーで指定したキーワード
            'num' => 300, //取得ユーザー数（最大300件）
        ];

        // // TwproAPIのリクエスト残り回数を取得
        $this->checkTwproLimit();

        // リクエスト残り回数が0の場合は処理を終了
        if (!$this->remain_count) {
            echo "Twproのリクエスト上限に達しました";
            logger()->info("Twproのリクエスト上限に達しました");
            return;
        }
        // TwproAPIでユーザーを検索し、該当データを保存
        $users_json = $this->requestUsersByTwpro();

        $users_count = $users_json->count;
        // 検索結果がある場合
        if ($users_count) {
            echo "Twproからユーザー情報を" . $users_count . "件取得<br>";
            logger()->info("Twproからユーザー情報を" . $users_count . "件取得");

            // 検索結果から必要なデータを抽出し、DBに保存
            $this->createRecord($users_json->users);
        } else {
            echo "検索結果は0件でした<br>";
            logger()->info("検索結果は0件でした");
        }
        return;
    }

    // TwproAPIでレートリミットを取得
    public function checkTwproLimit()
    {
        $url = 'https://twpro.jp/1/limit';

        $limit = curlRequestController::curl($url);
        $this->remain_count = $limit->rest; //	残りAPI利用可能回数
        echo "残り回数" . $this->remain_count . "回<br>";
        logger()->info("残り回数" . $this->remain_count . "回");

        $until = $limit->until; //次にrest値が復帰する日時(Unix時間)
        if ($until) {
            $this->until_at = date('Y/m/d H:i:s', $until);
            echo "利用再開は" . $this->until_at . "以降<br>";
            logger()->info("利用再開は" . $this->until_at . "以降");
        } else {
            $this->until_at = '';
        }
        return;
    }

    // TwproAPIでユーザーを検索
    public function requestUsersByTwpro()
    {
        // 検索パラメーターの配列をクエリ文字列に変換
        $query = http_build_query($this->params);
        // リクエストURLを生成
        $url = 'http://api.twpro.jp/1/search?' . $query;

        // cURLでAPIにリクエストし、ユーザー情報を取得
        $users_json = curlRequestController::curl($url);
        // dd($users_json);

        return $users_json;
    }


    // 検索結果からユーザー情報を取り出し、DBに保存
    public function createRecord($users_arr)
    {
        $create_count = 0;
        $update_count = 0;
        $query = [];
        // 検索結果がある場合
        if ($users_arr) {
            // 取得したユーザー件数分ループを回し、必要なデータを配列に格納
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

                // if (property_exists($user, 'status')) {
                //     $query = array_merge($query, [
                //         'tweet_id' => $user->status->id, //最新ユーザーID
                //         'tweet_text' => $user->status->text, //最新ユーザー文章
                //         'tweeted_at' => date('Y-m-d H:i:s', strtotime($user->status->created_at)), //最新ユーザー日時
                //     ]);
                // }

                // 該当のTwitterIDを持つレコードをテーブルから1件取得
                $target = DB::table('target_users')->where('twitter_id', $user->id)->first();
                if (!$target) {
                    // レコードがなければ新規作成
                    DB::table('target_users')->insert($query);
                    $create_count++;
                } else {
                    // レコードがあれば更新
                    // DB::table('target_users')->where('twitter_id', $user->id)->update($query);
                    $update_count++;
                }
            }
            $count['create'] = $create_count;
            $count['update'] = $update_count;

            echo $count['create'] . "件のユーザー情報を保存しました<br>";
            logger()->info($count['create'] . "件のユーザー情報を保存しました");

            echo $count['update'] . "件のユーザー情報が存在します<br>";
            logger()->info($count['update'] . "件のユーザー情報が存在します");
        }

        return;
    }
}
