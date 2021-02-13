<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\FetchTwitterUserController;

// TwproAPIでTwitterアカウントを取得
// FetchTwitterUserControllerクラスを継承
class FetchTwproController extends FetchTwitterUserController
{
    protected $remain_count = 0;
    protected $until_at = '';
    protected $params = [];

    // TwproAPIで仮想通貨関連Twitterアカウントを取得し、TwitterAPIで最新ツイートを取得
    public function fetchUsers()
    {
        // ユーザー検索オプションを指定
        $this->params = [
            'q' => $this->KEYWORDS, //親コントローラーで指定したキーワード
            'num' => 300, //取得ユーザー数（最大300件）
        ];

        // TwproAPIのリクエスト残り回数を取得
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

        // 最新ツイートが取得できなかったTwitterアカウントの最新ツイートをTwitterAPIから取得
        $this->fetchLatestTweet();
        return;
    }

    // TwproAPIでレートリミットを取得
    public function checkTwproLimit()
    {
        $url = 'https://twpro.jp/1/limit';

        $limit = curlRequestController::curl($url);
        $this->remain_count = $limit->rest; //	残りAPI利用可能回数
        // echo "残り回数" . $this->remain_count . "回<br>";
        // logger()->info("残り回数" . $this->remain_count . "回");

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


    // 最新ツイートが取得できなかったTwitterアカウントの最新ツイートをTwitterAPIから取得
    public function fetchLatestTweet()
    {
        $max_request = 100; // ツイート取得の最大リクエスト回数の初期値（上限は15分間に900回）

        //残り使用可能回数をTwitterAPIでチェック
        $status = \Twitter::get("application/rate_limit_status");

        // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
        if (property_exists($status, 'errors')) {
            $max_request = 0;
            echo "残り使用可能回数が取得できませんでした";
            logger()->info("残り使用可能回数が取得できませんでした");
        }

        // 検索APIの残り使用可能回数が存在する場合は回数の値を取得
        if (property_exists($status, 'resources')) {
            $limit_obj = $status->resources->statuses;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('statuses/user_timeline', $limit_arr)) {
                if (property_exists($limit_arr['statuses/user_timeline'], 'remaining')) {
                    $remain_count = $limit_arr['statuses/user_timeline']->remaining; // 残り使用回数
                    // 残り回数が初期値より少なければ、残り回数を最大リクエスト回数とする
                    if ($remain_count < $max_request) {
                        $max_request = $remain_count;
                    }
                }
            }
        }
        // 最新ツイートが未取得（'tweet_text'カラムが空）のターゲットユーザーレコードを取得
        $targets = TargetUser::where('tweet_text', null)->get();
        $count = count($targets);

        // 最新ツイートが未取得のターゲットがなければ処理を終了
        if ($count === 0) {
            return;
        }

        // 未取得件数がリクエスト上限より多い場合はリクエスト上限までとする
        if ($count > $max_request) {
            $count = $max_request;
        }
        $update_total = 0;
        // 最大リクエスト回数までループして各ターゲットユーザーの最新ツイートを取得
        for ($key = 0; $key < $count; $key++) {
            // ターゲットユーザー配列からTwitterIDを取得
            $target_id = $targets[$key]->twitter_id;
            // TwitterAPIの検索オプションを指定
            $params = array(
                'user_id' => $target_id,
                'count' => 1, // 最新ツイート1件を取得
                'exclude_replies' => true, //リプライを除外
                'include_rts' => true, //リツイートを除外
            );
            // TwitterAPIで対象ターゲットユーザーの最新ツイートを検索し、返却された検索結果を変数に格納
            $result = \Twitter::get("statuses/user_timeline", $params);

            // 返却された検索結果が空の場合は次の処理へ
            if (count($result) === 0) {
                continue;
            }

            // 対象ターゲットユーザーのレコードをテーブルから取得
            $record = TargetUser::where('twitter_id', $target_id)->first();
            if (!$record) {
                // レコードがなければ次の処理へ
                continue;
            }

            // エラーが返却された場合
            if (isset($result->error)) {
                // 非公開ユーザーの場合は最新ツイートに非公開設定である旨を保存
                if ($result->error === "Not authorized.") {
                    $query = [
                        'tweet_text' => 'ツイートは非公開です', //最新ツイートの文章
                    ];
                    $record->update($query);
                    $update_total++;
                }
                continue; //次の処理へ
            }
            if (isset($result->errors)) {
                if (isset($result->errors[0]->message) && $result->errors[0]->message === "Sorry, that page does not exist.") {
                    $query = [
                        'tweet_text' => 'このアカウントは存在しません', //最新ツイートの文章
                    ];
                    $record->update($query);
                    $update_total++;
                }
                if (isset($result->errors[0]->message) && $result->errors[0]->message === "Bad Authentication data.") {
                    $query = [
                        'tweet_text' => 'アカウントは凍結されています', //最新ツイートの文章
                    ];
                    $record->update($query);
                    $update_total++;
                }
                continue; //次の処理へ
            }
            // 最新ツイートが取得できた場合t
            if (isset($result[0]->text)) {
                // 該当のTwitterIDを持つレコードをテーブルから1件取得
                $record = TargetUser::where('twitter_id', $target_id)->first();

                // レコードを更新
                $query = [
                    'tweet_id' => $result[0]->id, //最新ツイートのID
                    'tweet_text' => $result[0]->text, //最新ツイートの文章
                    'tweeted_at' => date('Y-m-d H:i:s', strtotime($result[0]->created_at)), //最新ツイートの日時
                ];
                $record->update($query);
                $update_total++;
            }
        }
        echo  $update_total . "ユーザーの最新ツイートを更新しました";
        logger()->info($update_total . "ユーザーの最新ツイートを更新しました");
    }
}
