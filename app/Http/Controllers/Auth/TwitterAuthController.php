<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Socialite;

// TwitterAPIの処理を行うコントローラー
class TwitterAuthController extends Controller
{
    public function index(){
        return view('twitter');
    }
    // Twitter認証ページへのリダイレクト処理
    public function redirectToProvider()
    {
        // ユーザーをOAuthプロバイダへ送る
        return Socialite::driver('twitter')->redirect();
    }

    // Twitterから戻ってきた後のログイン認証処理
    public function handleProviderCallback()
    {
        try {
            // OAuthプロバイダからユーザー情報を取得
            $twitterUser = Socialite::driver('twitter')->user();
        } catch (Exception $e) {
            // 認証エラーの場合はトップページにリダイレクト
            return redirect('/');
        }

        // Twitterアカウント情報をもとにDBからユーザー情報を取得する
        // DBにユーザー情報がなければ、DBに新規登録する
        $authUser = $this->findOrUpdateUser($twitterUser);

        // ログイン処理
        // Auth::login($authUser, true);
        // dd($authUser);

        // ホーム画面にリダイレクトする
        return redirect('/twitter')->with('flash_message', __('Twitterアカウントを連携しました'));
    }

    // DBのユーザー情報取得 または アカウント新規作成の処理
    public function findOrUpdateUser($twitterUser)
    {
        // ログインユーザーのユーザーモデルを取得
        $user_id = Auth::id();
        $authUser = User::find($user_id);

        // ログインユーザーのtwitter_idが登録されていればそのユーザー情報を返却
        if ($authUser->twitter_id === $twitterUser->id) {
            return $authUser;
        }

        // ログインユーザーのtwitter_id情報がなければ、Twitterアカウント情報をテーブルに保存して返却
        $authUser->twitter_id = $twitterUser->id;
        $authUser->twitter_avatar = $twitterUser->avatar_original;
        $authUser->save();
        return $authUser;
    }

    // ログアウト処理
    public function logout()
    {
        // // ログアウト処理
        // Auth::logout();
        // // トップページにリダイレクトする
        // return redirect('/');
    }
}
