<?php

namespace App\Http\Controllers\Auth;

use App\TwitterUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Auth\UsersTwitterOAuth;
use App\Http\Controllers\FollowListController;

// TwitterAPIでのTwitterログイン処理を行うコントローラー
class TwitterAuthController extends Controller
{
    public function __construct()
    {
        // authミドルウェアの認証から除外するメソッドを指定
        $this->middleware('auth')->except(['handleProviderCallback', 'checkTwitterUserAuth']);
    }

    /**
     * ログインユーザーのTwitterアカウント情報取得処理
     */
    public function checkTwitterUserAuth()
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのTwitterアカウント情報をDBから取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();

        // Twitterアカウント情報を返却
        return $twitter_user;
    }

    /**
     * ログイン時にログインユーザーのTwitterアカウント情報とフォロー済みリストを更新
     */
    public function updateTwitterUser()
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのTwitterアカウント情報をDBから取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();

        // Twitterアカウント情報がある場合
        if ($twitter_user) {
            // ユーザーのTwitterアカウントでoAuth認証
            $connect = UsersTwitterOAuth::userOAuth($twitter_user);
            // Twitterアカウント情報取得用のパラメータを設定
            $params = array(
                'user_id' => $twitter_user->twitter_id,
                'include_entities' => false, //entitiesプロパティを取得するかどうか
            );
            // ログインユーザーのTwitterアカウント情報をTwitterAPIで取得
            $result = $connect->get("users/show", $params);

            // TwitterAPIで取得したユーザー名、スクリーンネーム、アバターにDBとの差分があれば更新
            $twitter_user->user_name = $result->name;
            $twitter_user->screen_name = $result->screen_name;
            $twitter_user->twitter_avatar = $result->profile_image_url;
            $twitter_user->save();

            // ログインユーザーのTwitterフォロー済みユーザーリストを更新
            FollowListController::loginUsersFollowList();
        }

        // Twitterアカウント情報を返却
        return $twitter_user;
    }

    /**
     * Twitter認証ページへのリダイレクト処理
     */
    public function redirectToProvider()
    {
        // ユーザーをOAuthプロバイダへ送る
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Twitter認証ページから戻ってきた後のログイン認証処理
     */
    public function handleProviderCallback()
    {
        try {
            // OAuthプロバイダでTwitter認証を行い、Twitterアカウント情報を取得
            $oauth_user = Socialite::driver('twitter')->user();
        } catch (Exception $e) {
            // エラーの場合はエラーメッセージを返却
            return redirect('/twitter')->with(
                [
                    'status' => __('Twitterアカウントの連携を中断しました'),
                    'type' => 'danger',
                    'timeout' => 4000,
                ]
            );
        }

        // Twitterアカウント情報をDBに新規登録または更新する
        $twitterUser = $this->updateOrCreateTwitterUser($oauth_user);

        // Twitterアカウントが重複している場合は登録せずにリダイレクトする
        if (!$twitterUser) {
            return redirect('/twitter')->with(
                [
                    'status' => __('そのTwitterアカウントはすでに登録されています'),
                    'type' => 'danger',
                    'timeout' => 4000,
                ]
            );
        }

        // 登録成功した場合はTwitter一覧画面へリダイレクトしてTwitterアカウント情報を再描画する
        // return redirect('/twitter');
        return redirect('/twitter')->with(
            [
                'status' => __('Twitterアカウントを連携しました'),
                'type' => 'success',
                'timeout' => 3000,
            ]
        );
    }

    /**
     * DBのユーザー情報取得 または アカウント新規作成の処理
     */
    public function updateOrCreateTwitterUser($oauth_user)
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();

        // Twitter認証したTwitterIDがテーブルに保存済みであればレコードを1件取得
        $registeredUser = TwitterUser::where('twitter_id', $oauth_user->id)->first();
        // 同一のTwitterIDが登録済みで、かつログインユーザー以外のものである場合は登録しない
        if ($registeredUser && $registeredUser->user_id !== $user_id) {
            return '';
        }

        // ログインユーザーIDに紐づくTwitterユーザー情報があれば情報更新し、なければ新規作成する
        $twitter_user = TwitterUser::updateOrCreate(
            ['user_id' => $user_id, 'twitter_id' => $oauth_user->id],
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

    /**
     * Twitterアカウント連携の解除
     */
    public function deleteTwitterUser()
    {
        $user_id = Auth::id();
        try {
            // ログインユーザーに紐づくTwitterアカウント情報を削除
            $twitterUser = TwitterUser::where('user_id', $user_id)->delete();
        } catch (Exception $e) {
            // エラーの場合はNotFoundエラーを返却
            return abort(404);
        }

        // 結果を返却する
        return $twitterUser;
    }
}
