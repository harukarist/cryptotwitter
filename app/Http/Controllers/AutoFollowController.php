<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Autofollow;
use App\TargetUser;
use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FollowListController;
use App\Http\Controllers\FollowTargetController;

// ユーザーの自動フォロー状況を表示、ユーザーからの自動フォロー適用・解除リクエスト受付
class AutoFollowController extends Controller
{
    // 自動フォローを適用
    public function applyAutoFollow()
    {
        // ログインユーザーのIDを取得
        $user_id = Auth::id();
        // ログインユーザーのレコードをユーザーIDで検索して取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();
        // 更新できなかった場合は404エラーを返却
        if (!$twitter_user) {
            return abort(404);
        }
        // 自動フォロー利用フラグをtrueに更新
        $twitter_user->use_autofollow = true;
        $twitter_user->save();

        // Twitterアカウント情報を返却
        return  $twitter_user;
    }

    // 自動フォローを解除
    public function cancelAutoFollow()
    {
        // ログインユーザーのIDを取得
        $user_id = Auth::id();
        // ログインユーザーのレコードをユーザーIDで検索して取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();
        // 更新できなかった場合は404エラーを返却
        if (!$twitter_user) {
            return abort(404);
        }
        // 自動フォロー利用フラグをfalseに更新
        $twitter_user->use_autofollow = false;
        $twitter_user->save();

        // Twitterアカウント情報を返却
        return  $twitter_user;
    }

    /**
     * ログインユーザーの自動フォロー累計数を集計して返却
     */
    public function countAutoFollow()
    {
        // ログインユーザーのTwitterアカウント情報を取得
        $twitter_user = Auth::user()->twitter_user()->first();
        // Twitterアカウントの登録がある場合、自動フォローログDBからログインユーザーの自動フォロー数の合計を取得
        if ($twitter_user) {
            $follow_total = DB::table('autofollow_logs')->where('twitter_user_id', $twitter_user->id)
                ->sum('follow_total');
        } else {
            $follow_total = 0;
        }
        return $follow_total;
    }


    /**
     * 自動フォロー履歴の一覧を表示
     */
    public function showAutoFollowList()
    {
        // ログインユーザーの自動フォロー履歴（autofollowsテーブルとリレーション先のtarget_userテーブルのレコード）を取得
        $autofollows = Autofollow::with('target_user')
            ->where('twitter_user_id', Auth::user()->twitter_user->id)
            ->paginate(10);

        // json形式で返却
        return $autofollows;
    }
}
