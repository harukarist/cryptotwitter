<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Follow;
use App\Autofollow;
use App\AutofollowLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EditAccountRequest;
/**
 * ログイン後の「アカウント設定」ページでの
 * ユーザー情報（お名前・メールアドレス）の変更リクエスト、及び
 * アカウントの削除リクエストを受け取り、
 * ユーザー情報の変更、削除を行うコントローラー
 */
class EditAccountController extends Controller
{
    public function __construct()
    {
        // 認証済みユーザーのみアクセス可能
        $this->middleware('auth');
    }

    /**
     * ユーザー情報（お名前・メールアドレス）編集メソッド
     * EditAccountRequestでバリデーションチェックを行い、結果がOKであれば
     * DBのユーザー情報と比較し、変更があれば更新する。
     */
    public function EditAccount(EditAccountRequest $request)
    {
        // usersテーブルからログインユーザーのユーザー情報を取得
        $user_id = Auth::id();
        $user = User::find($user_id);

        // 編集フォームに入力されたお名前・メールアドレスの情報がusersテーブルの内容と異なっている場合は更新する
        if ($user->name !== $request->name) {
            $user->name = $request->name;
            $user->save();
        }
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->save();
        }

        // ユーザー情報を返却
        return $user;
    }

    /**
     * ユーザーアカウント、及びユーザーに紐づく情報をDBから削除し、退会処理を行うメソッド
     */
    public function withdrawAccount()
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのユーザー情報を取得
        $user = User::find($user_id);
        // ログインユーザーのTwitterアカウント情報を取得
        $twitter_user = $user->twitter_user()->first();

        try {
            // ログインユーザーのTwitterアカウント情報が登録されている場合は関連レコードを削除
            // （論理削除するため、Eloquentを使用する）
            if ($twitter_user) {
                // ログインユーザーのフォローリストを削除
                Follow::where('twitter_user_id', $twitter_user->id)->delete();
                // ログインユーザーの自動フォローログを削除
                AutofollowLog::where('twitter_user_id', $twitter_user->id)->delete();
                // ログインユーザーの自動フォロー履歴を削除
                Autofollow::where('twitter_user_id', $twitter_user->id)->delete();
                // ログインユーザーのTwitterアカウント情報を削除
                $twitter_user->delete();
            }
            // ログインユーザーのユーザー情報を削除
            $user->delete();
        } catch (Exception $e) {
            // エラーの場合はNotFoundエラーを返却
            return abort(404);
        }

        // ステータスコードを返却
        return response()->json();
    }
}
