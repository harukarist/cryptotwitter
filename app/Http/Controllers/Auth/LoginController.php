<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * ログイン処理、ログアウト処理を行うコントローラー
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * vendor/laravel/framework/src/Illuminate/Foundation/Auth/AuthenticatesUsers.php の AuthenticatesUsersトレイトを使用
     */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // ログアウト以外は未認証ユーザーのみアクセス可能
        $this->middleware('guest')->except('logout');
    }


    /**
     * ログイン完了後の処理
     */
    protected function authenticated(Request $request, $user)
    {
        // ログイン完了後にログインユーザの情報を返却するよう、
        // AuthenticatesUsersトレイトのauthenticated()を上書き
        return $user;
    }

    /**
     * ログアウト時の処理.
     */
    protected function loggedOut(Request $request)
    {
        // ログアウト後にセッションを再生成するよう
        // AuthenticatesUsersトレイトのauthenticated()を上書き
        $request->session()->regenerate();

        // ログアウト処理の成功をフロント側に通知するため、ステータスコードを返却
        return response()->json();
    }
}
