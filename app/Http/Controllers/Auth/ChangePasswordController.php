<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangePasswordRequest;

/**
 * ログイン後のアカウント設定ページから
 * パスワードを変更するためのコントローラー
 */
class ChangePasswordController extends Controller
{
    public function __construct()
    {
        // 認証ユーザーのみアクセス可能
        $this->middleware('auth');
    }

    /**
     * ChangePasswordRequestでバリデーションチェックを行った後、
     * DBに保存してあるユーザーのパスワードを更新する処理
     * ※ 他のデバイスからのログインを全てログアウトするため、
     * 　app/Http/Kernel.php の AuthenticateSessionミドルウェアを有効にする
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        //ログインユーザーのレコードを取得
        $user = Auth::user();
        // パスワードをハッシュ化して保存
        $user->password = bcrypt($request->new_password);
        $user->save();

        // ユーザー情報を返却
        return response()->json(['status' => 200]);
    }
}
