<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Socialite;
use App\TwitterUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FollowListController;

// TwitterAPIでのTwitterログイン処理を行うコントローラー
class TwitterAuthController extends Controller
{
    public function __construct()
    {
        // authミドルウェアの認証から除外
        $this->middleware('auth')->except(['handleProviderCallback']);
    }

    // ログインユーザーのTwitterアカウント情報があれば返却
    public function checkTwitterUserAuth()
    {
        $user_id = Auth::id();
        // twitter_usersテーブルからログインユーザーのTwitterアカウント情報を取得
        $twitter_user = TwitterUser::select('id', 'user_name', 'screen_name', 'twitter_avatar', 'use_autofollow')
            ->where('user_id', $user_id)->first();

        if ($twitter_user->use_autofollow) {
            $follow_total = DB::table('autofollow_logs')->where('twitter_user_id', $twitter_user->id)
                ->sum('follow_total');
        } else {
            $follow_total = 0;
        }

        if ($twitter_user) {
            // ログインユーザーのTwitterフォローリストを更新
            FollowListController::loginUsersFollowList();
        }
        // Twitterアカウント情報を返却
        return [
            'twitter_user' => $twitter_user,
            'follow_total' => $follow_total,
        ];
    }

    // Twitter認証ページへのリダイレクト処理
    public function redirectToProvider()
    {
        // SocialiteからリダイレクトURLを取得
        // return Socialite::driver('twitter')->redirect()->getTargetUrl();

        // ユーザーをOAuthプロバイダへ送る
        return Socialite::driver('twitter')->redirect();
    }

    // Twitterから戻ってきた後のログイン認証処理
    public function handleProviderCallback()
    {
        try {
            // OAuthプロバイダからユーザー情報を取得
            $oauth_user = Socialite::driver('twitter')->user();
        } catch (Exception $e) {
            // 認証エラーの場合はトップページにリダイレクト
            return redirect('/')->with('flash_message', __('Twitterアカウントの連携ができませんでした'));
        }
        // dd($oauth_user);

        // Twitterアカウント情報をもとにDBからユーザー情報を取得する
        // DBにユーザー情報がなければ、DBに新規登録する
        $twitterUser = $this->updateOrCreateTwitterUser($oauth_user);

        // twitter画面にリダイレクトする
        return redirect('/twitter')->with('flash_message', __('Twitterアカウントを連携しました'));
    }

    // DBのユーザー情報取得 または アカウント新規作成の処理
    public function updateOrCreateTwitterUser($oauth_user)
    {
        // ログインユーザーIDに紐づくTwitterユーザー情報があればDBから取得し、なければ新規作成
        $user_id = Auth::id();
        $twitter_user = TwitterUser::updateOrCreate(
            ['user_id' => $user_id],
            // 最新の情報で更新
            [
                'twitter_id' => $oauth_user->id,
                'twitter_token' => $oauth_user->token,
                'twitter_token_secret' => $oauth_user->tokenSecret,
                'user_name' => $oauth_user->name,
                'screen_name' => $oauth_user->nickname,
                'twitter_avatar' => $oauth_user->avatar,
            ]
        );
        return $twitter_user;
    }

    // Twitterアカウント連携の解除
    public function delete()
    {
        $user_id = Auth::id();
        // ログインユーザーに紐づくTwitterアカウント情報を削除
        TwitterUser::where('user_id', $user_id)->delete();

        // twitter画面にリダイレクトする
        return redirect('/twitter')->with('flash_message', __('Twitterアカウント連携を解除しました'));
    }
}
