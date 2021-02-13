<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Support\Facades\DB;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Controller;

// TwproAPIでTwitterアカウントを取得
class FetchTargetTweetController extends Controller
{
    // 最新ツイートが取得できなかったTwitterアカウントの最新ツイートをTwitterAPIから取得
    static public function fetchLatestTweet()
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
