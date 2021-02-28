<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangePasswordRequest;

/**
 * ログイン後の「アカウント設定」ページでの
 * ユーザーからのパスワード変更リクエストを受け取り、
 * ログインユーザーのパスワードを変更するためのコントローラー
 */
class ChangePasswordController extends Controller
{
    public function __construct()
    {
        // 認証済みユーザーのみアクセス可能
        $this->middleware('auth');
    }

    /**
     * ChangePasswordRequestでバリデーションチェックを行い、結果がOKであれば
     * DBに保存してあるユーザーのパスワードを更新するメソッド
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        // ログインユーザーのレコードをusersテーブルから取得
        $user = Auth::user();
        // パスワード変更フォームに入力された新しいパスワードをハッシュ化して保存
        $user->password = bcrypt($request->new_password);
        $user->save();

        // レスポンスを返却
        return response()->json(['status' => 200]);

        // 他のデバイスからのログインを全てログアウトするため、
        // app/Http/Kernel.php の AuthenticateSessionミドルウェアを有効にする
    }
}
