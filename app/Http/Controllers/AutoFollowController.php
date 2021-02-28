<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Autofollow;
use App\TwitterUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * ユーザーの自動フォロー履歴の返却、及び
 * ユーザーからの自動フォロー適用申込み・自動フォロー解除を受け付けるコントローラー
 */
class AutoFollowController extends Controller
{
    /**
     * 自動フォローを適用しているユーザーの自動フォロー履歴を表示するため、
     * ユーザーの自動フォロー履歴データをDBから取得してJSON形式で返却するメソッド。
     * 初回表示時などキーワード指定がない場合はDBから全データをページネーション形式で返却し、
     * キーワード検索フォームからキーワードが送信された場合は、該当キーワードを含む
     * 自動フォロー済み仮想通貨アカウントのデータのみをページネーション形式で返却する。
     */
    public function showAutoFollowList(Request $request)
    {
        // キーワード検索フォームからの検索リクエストがある場合は、検索キーワードを格納
        if ($request->search) {
            $search_word = $request->search;
        } else {
            $search_word = '';
        }

        // ログインユーザーに紐づくtwitter_userテーブルのidを取得
        $twitter_id = Auth::user()->twitter_user->id;

        // 検索キーワードがある場合
        if (!empty($search_word)) {
            // リレーション先テーブル'target_user'の検索条件を指定するため
            // whereHasで部分一致検索するクエリを変数に格納
            $whereHas = function ($q) use ($search_word) {
                $q->where('twitter_user_id', Auth::user()->twitter_user->id)
                    ->where('profile_text', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('tweet_text', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('screen_name', 'LIKE', '%' . $search_word . '%')
                    ->orwhere('user_name', 'LIKE', '%' . $search_word . '%');
            };

            // 検索キーワードを含む自動フォロー履歴をページネーション形式で取得
            $items = Autofollow::with('target_user')
                ->where('twitter_user_id', $twitter_id)
                ->whereHas('target_user', $whereHas)
                ->distinct('target_id') //重複データを除く
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            // json形式で返却
            return $items;
        }

        // 検索キーワードの指定がない場合
        // ログインユーザーの自動フォロー履歴（autofollowsテーブルとリレーション先のtarget_userテーブルのレコード）をページネーション形式で取得
        $items = Autofollow::with('target_user')
            ->where('twitter_user_id',  $twitter_id)
            ->distinct('target_id') //重複データを除く
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        // json形式で返却
        return $items;
    }

    /**
     * 仮想通貨アカウント一覧画面でユーザーが「自動フォロー機能をON」をクリックした際の
     * リクエストを受け、DB内の該当ユーザーの自動フォローフラグをtrueに変更し、
     * 該当ユーザーのTwitterアカウントで自動フォローを行うようにするメソッド
     */
    public function applyAutoFollow()
    {
        // ログインユーザーのIDを取得
        $user_id = Auth::id();
        // twitter_usersテーブルからログインユーザーのレコードをユーザーIDで検索して取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();
        // 取得できなかった場合は404エラーを返却
        if (!$twitter_user) {
            return abort(404);
        }
        // ログインユーザーの自動フォロー利用フラグをtrueに変更して保存
        $twitter_user->use_autofollow = true;
        $twitter_user->save();

        // Twitterアカウント情報を返却
        return  $twitter_user;
    }

    /**
     * 仮想通貨アカウント一覧画面でユーザーが「自動フォロー機能を解除する」をクリックした際の
     * リクエストを受け、DB内の該当ユーザーの自動フォローフラグをfalseに変更し、
     * 該当ユーザーのTwitterアカウントで自動フォローを実行しないようにするメソッド
     */
    public function cancelAutoFollow()
    {
        // ログインユーザーのIDを取得
        $user_id = Auth::id();
        // twitter_usersテーブルからログインユーザーのレコードをユーザーIDで検索して取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();
        // 更新できなかった場合は404エラーを返却
        if (!$twitter_user) {
            return abort(404);
        }
        // ログインユーザーの自動フォロー利用フラグをfalseに更新して保存
        $twitter_user->use_autofollow = false;
        $twitter_user->save();

        // Twitterアカウント情報を返却
        return  $twitter_user;
    }
}
