<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Autofollow;
use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// ユーザーの自動フォロー状況を表示、ユーザーからの自動フォロー適用・解除リクエスト受付
class AutoFollowController extends Controller
{
    /**
     * 自動フォロー履歴の一覧を表示
     */
    public function showAutoFollowList(Request $request)
    {
        // 検索リクエストがあった場合は検索キーワードを格納
        if ($request->search) {
            $search_word = $request->search;
        } else {
            $search_word = '';
        }

        $twitter_id = Auth::user()->twitter_user->id;

        // 検索キーワードがある場合
        if (!empty($search_word)) {
            // withメソッドで取得するリレーション先テーブル'target_user'の検索条件を指定するため
            // whereHasでクエリを変数に格納
            $whereHas = function ($q) use ($search_word) {
                $q->where('twitter_user_id', Auth::user()->twitter_user->id)
                    ->where('profile_text', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('tweet_text', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('screen_name', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('user_name', 'LIKE', '%' . $search_word . '%');
            };

            // 検索キーワードを含む自動フォロー履歴をページネーション形式で取得して返却
            $items = Autofollow::with('target_user')
                ->where('twitter_user_id', $twitter_id)
                ->whereHas('target_user', $whereHas)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            return $items;
        }


        // 検索キーワードの指定がない場合
        // ログインユーザーの自動フォロー履歴（autofollowsテーブルとリレーション先のtarget_userテーブルのレコード）を取得
        $items = Autofollow::with('target_user')
            ->where('twitter_user_id',  $twitter_id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // json形式で返却
        return $items;
    }

    /**
     * ログインユーザーの自動フォローを利用開始
     */
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

    /**
     * ログインユーザーの自動フォローを解除
     */
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
}
