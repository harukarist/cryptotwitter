<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Socialite;

// TwitterAPIの処理を行うコントローラー
class TwitterAuthController extends Controller
{
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
        $authUser = $this->findOrCreateUser($twitterUser);

        // ログイン処理
        Auth::login($authUser, true);

        // ホーム画面にリダイレクトする
        return redirect()->route('home');
    }

    // DBのユーザー情報取得 または アカウント新規作成の処理
    public function findOrCreateUser($twitterUser)
    {
        // DBにユーザーのtwitter_idが登録されていればそのユーザー情報を返却
        $authUser = User::where('twitter_id', $twitterUser->id)->first();
        if ($authUser) {
            return $authUser;
        }

        //DBにユーザー情報がなければ、Twitterアカウント情報をDBに新規登録
        return User::create([
            'name' => $twitterUser->nickname,
            'twitter_id' => $twitterUser->id,
            'avatar' => $twitterUser->avatar_original,
        ]);
    }

    // ログアウト処理
    public function logout()
    {
        // ログアウト処理
        Auth::logout();
        // トップページにリダイレクトする
        return redirect('/');
    }
}
