<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\TargetUser;
use App\TwitterUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FollowListController;
use App\Http\Controllers\Auth\UsersTwitterOAuth;
use App\Http\Controllers\FollowTargetController;

/**
 * 自動フォローをONにしているユーザーのTwitterアカウントで
 * 仮想通貨アカウントの自動フォローを実行するコマンド
 */
class AutoFollow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'follow:autofollow';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = '自動フォロー適用中のユーザーの自動フォローを実行';

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
        $MAX_PER_ONCE = 15; //1回のコマンド実行で自動フォローするアカウント数の上限（15分に15回まで）
        $MAX_PER_DAY = 400; //1日あたりのフォロー数の上限（1日あたり400件）
        // 各アカウントのフォロー数のリミットは1日あたり400件
        // https://help.twitter.com/ja/rules-and-policies/twitter-limits

        $today = Carbon::today(); // 今日の日付を取得

        // ログファイルに書き込む
        logger()->info('>>>> 自動フォロー処理バッチを実行します');

        // 自動フォローを利用しているユーザー(twitter_usersテーブルのuse_autofollowカラムがtrue)の
        // Twitterアカウント一覧を取得
        $twitter_users = TwitterUser::where('use_autofollow', true)->get();

        // 仮想通貨アカウント一覧（自動フォロー対象のターゲット）をtarget_usersテーブルから取得
        // ツイートIDがnullのレコード（凍結アカウント、削除済みアカウント、鍵付きアカウント、ツイートが1件もないアカウント）は除く
        $target_users = TargetUser::select('id', 'twitter_id')
            ->whereNotNull('tweet_id')->get();

        // 自動フォローを利用しているユーザーのTwitterアカウントで1件ずつ自動フォロー処理
        foreach ($twitter_users as $twitter_user) {
            // 対象ユーザーの自動フォローログのうち、バッチ処理実行日の日付の最新レコードを取得
            // フォローを行うエンドポイント（"friendships/create")は、"application/rate_limit_status"での
            // 上限取得ができないため、DBでTwitterAPIへのリクエスト回数を管理し、残り可能回数をチェックする。
            $log = DB::table('autofollow_logs')->whereDate('created_at', $today)
                ->where('twitter_user_id', $twitter_user->id)
                ->orderBy('created_at', 'DESC')->first();

            // 今日のログレコードが存在する場合はその日の残り回数を変数に格納
            if ($log) {
                $remain_num = $log->remain_num;
            } else {
                // 今日のログレコードが未作成の場合は1日の上限を残り回数にセット
                $remain_num = $MAX_PER_DAY;
            }

            // ユーザーの残り回数が0以下の場合は次のユーザーの自動フォローへ進む
            if ($remain_num < 0) {
                continue; //次のループへ
            } else if ($remain_num < $MAX_PER_ONCE) {
                // ユーザーの残り回数が1回のリクエスト上限より少ない場合は、残り回数をリクエスト上限として設定
                $max_requests = $remain_num;
            } else {
                // その他の場合は1回のリクエスト上限を設定
                $max_requests = $MAX_PER_ONCE;
            }
            // ユーザーTwitterアカウントのフォロー済みアカウント一覧をfollowsテーブルに保存するメソッドを実行
            FollowListController::createOrUpdateFollowList($twitter_user);

            // フォロー済みの仮想通貨アカウントのコレクションをfollowsテーブルから取得
            $follows = $twitter_user->follows()->get();

            // 仮想通貨アカウント一覧からフォロー済みアカウントを除いた自動フォロー対象のアカウントオブジェクトを取得
            $diff = $target_users->diff($follows);

            // 自動フォロー対象のアカウントオブジェクトからTwitterIDのみ抽出し、配列に変換
            $target_ids = $diff->pluck('twitter_id')->toArray();

            // 自動フォロー対象のTwitterIDが存在しない場合
            if (!$target_ids) {
                dump("{$twitter_user->user_name}さんがフォローできる仮想通貨アカウントがありませんでした");
                logger()->info("{$twitter_user->user_name}さんがフォローできる仮想通貨アカウントがありませんでした");
                continue; // 次のユーザーの自動フォローへ進む
            }

            // 自動フォロー対象のTwitterIDがある場合は、配列に格納したTwitterIDの個数をカウント
            $ids_count = count($target_ids);
            // 自動フォロー対象のTwitterIDの個数がリクエスト上限より少ない場合は、TwitterIDの個数をリクエスト上限に変更
            if ($ids_count < $max_requests) {
                $max_requests = $ids_count;
                dump("{$twitter_user->user_name}さんがフォローできる仮想通貨アカウントは残り{$ids_count}個です");
                logger()->info("{$twitter_user->user_name}さんがフォローできる仮想通貨アカウントは残り{$ids_count}個です");
            }

            // ユーザーのTwitterアカウントでoAuth認証するメソッドを実行
            $connect = UsersTwitterOAuth::userOAuth($twitter_user);

            $follow_total = 0; // 今回のフォロー合計数をカウントする変数

            // フォロー合計数が上限に達するまでターゲットの自動フォローを実行
            for ($i = 1; $follow_total < $max_requests; $i++) {
                // TwitterIDの配列からキーをランダムに1件抽出
                $key = array_rand($target_ids);
                // ターゲット配列から抽出したキーを持つターゲットのTwitterIDを取得
                $target_id = $target_ids[$key];

                // ユーザーとターゲットのTwitterIDを指定してTwitterAPIでフォローを行うメソッドを実行
                $result = FollowTargetController::createFollow($twitter_user, $target_id, $connect);

                // ターゲットをフォローした場合（戻り値の$result['is_done']がtrueの場合）
                if ($result['is_done']) {
                    // ターゲットのtarget_usersテーブル上の主キー'id'を取得
                    $target = TargetUser::select('id')->where('twitter_id', $target_id)->first();
                    // 自動フォローリストに保存
                    DB::table('autofollows')->insert([
                        'twitter_user_id' => $twitter_user->id,
                        'target_id' => $target->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    // 今回のフォロー合計数を1増やす
                    $follow_total++;
                }
                dump($result);
                logger()->info($result);
            }
            dump("{$twitter_user->user_name}さんのアカウントで {$follow_total}件自動フォローしました");
            logger()->info("{$twitter_user->user_name}さんのアカウントで {$follow_total}件自動フォローしました");

            // 残り回数から今回のフォロー合計数を減らす
            $remain_num -= $follow_total;

            // 自動フォローログをDBに保存
            DB::table('autofollow_logs')->insert([
                'twitter_user_id' => $twitter_user->id,
                'follow_total' => $follow_total,
                'remain_num' => $remain_num,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        logger()->info('自動フォロー処理バッチを実行しました <<<<');
    }
}
