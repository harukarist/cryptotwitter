<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangeAccountRequest;

class ChangeAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ユーザー名、メールアドレスの変更
    //ChangePasswordRequestでバリデーションチェック
    public function changeAccount(ChangeAccountRequest $request)
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのユーザー情報を取得
        $user = User::find($user_id);

        //DBの内容と変更があれば更新
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

    // ユーザーアカウント及び関連情報の削除
    public function withdrawAccount()
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのユーザー情報を取得
        $user = User::find($user_id);
        // ログインユーザーのTwitterアカウント情報を取得
        $twitter_user = $user->twitter_user()->first();

        // ログインユーザーのフォローリストを削除
        DB::table('follows')->where('twitter_user_id', $twitter_user->id)->delete();
        // ログインユーザーの自動フォローログを削除
        DB::table('autofollow_logs')->where('twitter_user_id', $twitter_user->id)->delete();
        // ログインユーザーのTwitterアカウント情報を削除
        $twitter_user->delete();
        // ログインユーザーのユーザー情報を削除
        $user->delete();
    }
}
