<?php

namespace App\Console\Commands;

use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuthException;

// TwproAPIから取得した仮想通貨アカウントなど、最新ツイートが取得できなかった仮想通貨アカウントの最新ツイートをTwitterAPIから取得するコマンド
class FetchTargetsTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:targetsTweet';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'ツイート未取得の仮想通貨アカウントの最新ツイートをTwitterAPIから取得し、DBに保存';

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
     * コマンドで実行する処理
     * @return mixed
     */
    public function handle()
    {
        // ツイート取得の最大リクエスト回数の初期値（上限は15分間に900回）
        $MAX_REQUEST = 900;

        //ログファイルに書き込む
        logger()->info('>>>> 仮想通貨アカウントの未取得ツイート保存バッチを実行します');


        // TwitterAPIのリクエスト残り回数を取得
        $limit_count = $this->checkLimit();

        // リクエスト残り回数が初期値より少なければ、リクエスト残り回数をリクエスト回数とする
        if ($limit_count < $MAX_REQUEST) {
            $remain_count = $limit_count;
        } else {
            // リクエスト残り回数が初期値より大きければ、初期値をリクエスト回数とする
            $remain_count = $MAX_REQUEST;
        }
        dump("リクエスト回数はあと" . $remain_count . "回");
        logger()->info("リクエスト回数はあと" . $remain_count . "回");

        // 最新ツイートが未取得（'tweet_text'カラムが空）のターゲットユーザーレコードを取得
        $targets = TargetUser::where('tweet_text', null)->get();

        // ターゲット
        $this->requestTargetsTweets($targets, $remain_count);
        logger()->info('仮想通貨アカウントの未取得ツイート保存バッチを実行しました <<<<');
    }

    /**
     * TwitterAPIでレートリミットを取得
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
            $limit_obj = $status->resources->statuses;
            $limit_arr = (array)$limit_obj;

            if (array_key_exists('statuses/user_timeline', $limit_arr)) {
                if (property_exists($limit_arr['statuses/user_timeline'], 'remaining')) {
                    $limit_count = $limit_arr['statuses/user_timeline']->remaining; // 残り使用回数
                    logger()->info("残り:{$limit_count}回");
                    return $limit_count;
                }
            }
        }
        return 0;
    }
    /**
     * TwitterAPIでキーワードを含むTwitterアカウントを検索
     */
    public function requestTargetsTweets($targets, $remain_count)
    {
        $update_total = 0;

        // 最新ツイートが未取得のターゲットレコード件数を取得
        $targets_count = count($targets);

        // 最新ツイートが未取得のターゲットがなければ処理を終了
        if ($targets_count === 0) {
            return;
        }
        // 未取得のターゲット件数がリクエスト回数より多い場合はリクエスト回数を上限とする
        if ($targets_count > $remain_count) {
            $targets_count = $remain_count;
        }
        // 最大リクエスト回数までループして各ターゲットユーザーの最新ツイートを取得
        for ($key = 0; $key < $targets_count; $key++) {
            // ターゲットユーザー配列からTwitterIDを取得
            $target_id = $targets[$key]->twitter_id;
            // TwitterAPIの検索オプションを指定
            $params = array(
                'user_id' => $target_id,
                'count' => 1, // 最新ツイート1件を取得
                'exclude_replies' => true, //リプライを除外
                'include_rts' => true, //リツイートを除外
            );

            try {
                // TwitterAPIで対象ターゲットユーザーの最新ツイートを検索し、返却された検索結果を変数に格納
                $result = Twitter::get("statuses/user_timeline", $params);
            } catch (TwitterOAuthException $e) {
                dump("エラーが発生しました");
                logger()->info("エラーが発生しました");
                break;
            }
            // 返却された検索結果が空の場合は次の処理へ
            if (!$result) {
                continue;
            }
            // 対象の仮想通貨アカウントのレコードをテーブルから取得
            $record = TargetUser::where('twitter_id', $target_id)->first();
            if (!$record) {
                // レコードがなければ次の処理へ
                continue;
            }

            // TwitterAPIからの返却値にerrorが含まれる場合
            if (isset($result->error)) {
                // 非公開ユーザーの場合は最新ツイートに非公開設定である旨を保存
                if ($result->error === "Not authorized.") {
                    $query = [
                        'tweet_text' => 'ツイートは非公開です', // 最新ツイートカラムにメッセージを格納
                    ];
                    $record->update($query);
                    $update_total++;
                }
                continue; //次の処理へ
            }
            // TwitterAPIからの返却値にerrorsが含まれる場合
            if (isset($result->errors)) {
                if (isset($result->errors[0]->message) && $result->errors[0]->message === "Sorry, that page does not exist.") {
                    $query = [
                        'tweet_text' => 'このアカウントは存在しません', // 最新ツイートカラムにメッセージを格納
                    ];
                    $record->update($query);
                    $update_total++;
                }
                if (isset($result->errors[0]->message) && $result->errors[0]->message === "Bad Authentication data.") {
                    $query = [
                        'tweet_text' => 'アカウントは凍結されています', // 最新ツイートカラムにメッセージを格納
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

                // 最新ツイートを更新
                $query = [
                    'tweet_id' => $result[0]->id, //最新ツイートのID
                    'tweet_text' => $result[0]->text,
                    'tweeted_at' => date('Y-m-d H:i:s', strtotime($result[0]->created_at)), //最新ツイートの日時
                ];
                $record->update($query);
                $update_total++;
            }
        }
        dump("仮想通貨アカウントの最新ツイートを{$update_total}件更新しました");
        logger()->info("仮想通貨アカウントの最新ツイートを{$update_total}件更新しました");
    }
}
