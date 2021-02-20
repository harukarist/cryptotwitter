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
 * 仮想通貨アカウントの自動フォローを実行するクラス
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
     *
     * @return mixed
     */
    // コマンドで実行する処理
    public function handle()
    {
        // ログファイルに書き込む
        logger()->info('>>>> 自動フォロー処理バッチを実行します');

        // 自動フォローを利用しているユーザーのTwitterアカウント一覧を取得
        $twitter_users = TwitterUser::where('use_autofollow', true)->get();

        // ターゲット一覧（自動フォロー対象のTwitterアカウント一覧）を取得
        // ツイートIDがnullのレコードは除く（凍結アカウント、削除済みアカウント、鍵付きアカウント、ツイートが1件もないアカウントのため、自動フォロー対象外とする）
        $target_users = TargetUser::select('id', 'twitter_id')
            ->whereNotNull('tweet_id')->get();

        $MAX_REQUESTS = 15; //自動フォローするアカウント数の上限（15分に15回まで）

        // 今日の日付を取得
        $today = Carbon::today();

        // 自動フォローを利用しているユーザーを1件ずつ自動フォロー処理
        foreach ($twitter_users as $twitter_user) {
            // 対象ユーザーの自動フォローログのうち、バッチ処理実行日の日付の最新レコードを取得
            // フォローを行うエンドポイント（"friendships/create")は"application/rate_limit_status"での
            // 上限取得ができないため、DBでTwitterAPIへのリクエスト回数を管理し、残り可能回数をチェックする。
            $log = DB::table('autofollow_logs')->whereDate('created_at', $today)
                ->where('twitter_user_id', $twitter_user->id)
                ->orderBy('created_at', 'DESC')->first();

            // 今日のログレコードが存在する場合はその日の残り回数を変数に格納
            if ($log) {
                $remain_num = $log->remain_num;
            } else {
                // 今日のログレコードが未作成の場合は1日の上限（1000件）を残り回数にセット
                $remain_num = 1000;
            }

            // ユーザーの残り回数が0以下の場合は処理を終了
            if ($remain_num < 0) {
                return;
            } else if ($remain_num < $MAX_REQUESTS) {
                // ユーザーの残り回数が1回のリクエスト上限より少ない場合は、リクエスト上限を残り回数に変更
                $MAX_REQUESTS = $remain_num;
            }

            // ユーザーのTwitterアカウントでoAuth認証するメソッドを実行
            $connect = UsersTwitterOAuth::userOAuth($twitter_user);

            // ユーザーTwitterアカウントのフォロー済みアカウント一覧をfollowsテーブルに保存するメソッドを実行
            FollowListController::createOrUpdateFollowList($twitter_user);
            // フォロー済みアカウントのコレクションをfollowsテーブルから取得
            $follows = $twitter_user->follows()->get();

            // ターゲット一覧からフォロー済みアカウントを除いたオブジェクトを取得
            $diff = $target_users->diff($follows);

            // オブジェクトからTwitterIDのみ抽出し、配列に変換
            $target_ids = $diff->pluck('twitter_id')->toArray();

            $follow_total = 0;

            for ($i = 1; $i <= $MAX_REQUESTS; $i++) {
                // TwitterIDの配列からキーをランダムに1件抽出
                $key = array_rand($target_ids);
                // ターゲット配列から抽出したキーを持つターゲットのTwitterIDを取得
                $target_id = $target_ids[$key];
                // ユーザーとターゲットのTwitterIDを指定してフォローを行うメソッドを実行
                $result = FollowTargetController::createFollow($twitter_user, $target_id);
                // ターゲットをフォローした場合
                if ($result['do_follow']) {
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

            // 残り回数（1日最大1000件）から今回のフォロー合計数を減らす
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
