<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\FetchTargetTweetController;

// TwproAPIで仮想通貨アカウントを取得
class FetchTwproController extends Controller
{
    // 仮想通貨アカウントの検索キーワード
    public $KEYWORDS = ['仮想通貨', '暗号資産', 'ビットコイン'];

    // TwproAPIで仮想通貨関連Twitterアカウントを取得、TwitterAPIで最新ツイートを取得し、保存する処理
    public function fetchUsers()
    {
        $create_total = 0;
        $update_total = 0;
        $req_count = 0;

        foreach ($this->KEYWORDS as $keyword) {
            // TwproAPIのリクエスト残り回数を取得
            $limit = $this->checkTwproLimit();

            $remain_count = $limit->rest; //残りAPI利用可能回数
            echo "残り回数は{$remain_count}<br>";
            logger()->info("残り回数は{$remain_count}");

            $until = $limit->until; //次にrest値が復帰する日時(Unix時間)

            // リクエスト残り回数が0の場合
            if (!$remain_count) {
                echo "Twproのリクエスト上限に達しました";
                logger()->info("Twproのリクエスト上限に達しました");
                // 利用再開時刻が指定されている場合
                if ($until) {
                    $date = date('Y/m/d H:i:s', $until);
                    echo "利用再開は{$date}以降<br>";
                    logger()->info("利用再開は{$date}以降");
                    // 指定の時刻まで処理を遅延する
                    time_sleep_until($until);
                    echo "Twproのリクエストを再開";
                    logger()->info("Twproのリクエストを再開");
                }
            }

            // ユーザー検索オプションを指定
            $params = [
                'q' => $keyword, //親コントローラーで指定したキーワード
                'num' => 300, //取得ユーザー数（最大300件）
            ];

            echo "「{$keyword}」でTwitterアカウントを検索<br>";
            logger()->info("「{$keyword}」でTwitterアカウントを検索");
            // TwproAPIでユーザーを検索
            $users_json = $this->requestUsersByTwpro($params);
            $req_count++;

            // TwproAPIから返却された検索結果の件数をカウント
            $users_count = $users_json->count;
            // 検索結果がある場合
            if ($users_count) {
                echo "Twproからユーザー情報を{$users_count}件取得<br>";
                logger()->info("Twproからユーザー情報を{$users_count}件取得");

                // 検索結果から必要なデータを抽出し、DBに保存
                $count = $this->createRecord($users_json->users);
                // 新規保存したレコード件数、更新したレコード件数を加算
                $create_total += $count['create'];
                $update_total += $count['update'];
            } else {
                echo "検索結果は0件でした<br>";
                logger()->info("検索結果は0件でした");
            }
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

        // 最新ツイートが取得できなかったTwitterアカウントの最新ツイートをTwitterAPIから取得
        FetchTargetTweetController::fetchLatestTweet();
        return;
    }

    // TwproAPIでレートリミットを取得
    public function checkTwproLimit()
    {
        $url = 'https://twpro.jp/1/limit';

        $limit = curlRequestController::curl($url);
        return $limit;
    }

    // TwproAPIでユーザーを検索
    public function requestUsersByTwpro($params)
    {
        // 検索パラメーターの配列をクエリ文字列に変換
        $query = http_build_query($params);
        // リクエストURLを生成
        $url = 'http://api.twpro.jp/1/search?' . $query;

        // cURLでAPIにリクエストし、ユーザー情報を取得
        $users_json = curlRequestController::curl($url);

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
