<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\TargetUser;
use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FollowListController;
use App\Http\Controllers\FollowTargetController;

// 自動フォローを行うコントローラー
class FetchAutoFollowController extends Controller
{
    /**
     * 自動フォローを行うバッチ処理
     */
    public function autoFollow()
    {
        // 自動フォロー機能を申し込んでいるユーザーのTwitterアカウント一覧を取得
        $twitter_users = TwitterUser::where('use_autofollow', true)->get();

        // ターゲット一覧（フォロー対象のTwitterアカウント一覧）を取得
        $target_users = TargetUser::select('id', 'twitter_id')->get();

        $max_requests = 15; //自動フォローするアカウント数の上限（15分に15回まで）
        $today = Carbon::today();

        foreach ($twitter_users as $twitter_user) {
            // ユーザーTwitterアカウントのフォロー済み一覧リストをフォローテーブルに保存
            FollowListController::createOrUpdateFollowList($twitter_user);
            // フォロー済みアカウントのコレクションを取得
            $follows = $twitter_user->follows()->get();

            // ユーザーの今日の日付のログレコードを取得
            $log = DB::table('autofollow_logs')->whereDate('created_at', $today)
                ->where('twitter_user_id', $twitter_user->id)
                ->orderBy('created_at', 'DESC')->first();

            if ($log) {
                // 今日のログレコードが存在する場合は残り回数を変数に格納
                $remain_num = $log->remain_num;
            } else {
                // 今日のログレコードが未作成の場合は1日の上限（1000件）を残り回数にセット
                $remain_num = 1000;
            }
            // ユーザーの残り回数が上限より少ない場合は、上限を残り回数に変更
            if ($remain_num < $max_requests) {
                $max_requests = $remain_num;
            }

            // ターゲット一覧からフォロー済みアカウントを除いたオブジェクトを取得
            $diff = $target_users->diff($follows);
            // オブジェクトからTwitterIDのみ抽出し、配列に変換
            $target_ids = $diff->pluck('twitter_id')->toArray();

            for ($i = 1; $i <= $max_requests; $i++) {
                // TwitterIDの配列からキーをランダムに1件抽出
                $key = array_rand($target_ids);
                // ターゲット配列から抽出したキーを持つTwitterIDを取得
                $target_id = $target_ids[$key];
                // 該当のTwitterIDをフォロー
                $result = FollowTargetController::createFollow($twitter_user, $target_id);

                $target = TargetUser::where('twitter_id', $target_id)->first();
                // 自動フォローリストに保存
                DB::table('autofollows')->insert([
                    'twitter_user_id' => $twitter_user->id,
                    'target_id' => $target->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                logger()->info("{$result['message']}");
            }
            logger()->info("{$twitter_user->user_name}さんのアカウントで {$max_requests}件自動フォローしました");

            $remain_num -= $i;

            // 自動フォローログをDBに保存
            DB::table('autofollow_logs')->insert([
                'twitter_user_id' => $twitter_user->id,
                'follow_total' => $max_requests,
                'remain_num' => $remain_num,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
