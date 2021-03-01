<?php

namespace App\Http\Controllers\Auth;

use App\Follow;
use App\Autofollow;
use App\TwitterUser;
use App\AutofollowLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\FollowListController;
use App\Http\Controllers\Auth\UsersTwitterOAuth;

/**
 * TwitterAPIでのTwitterログイン処理、及び
 * ユーザーのTwitterアカウント情報の取得・更新を行うコントローラー
 */
class TwitterAuthController extends Controller
{
    public function __construct()
    {
        // TwitterAPIからのコールバックを受け取るメソッド、
        // ユーザーのTwitterアカウント情報を取得するメソッドを
        // authミドルウェアの認証から除外する
        $this->middleware('auth')->except(['handleProviderCallback', 'checkTwitterUserAuth']);
    }

    /**
     * アプリケーションの初期描画時にユーザーのTwitterアカウント情報を取得するメソッド。
     * ユーザーのTwitterアカウントがDBに保存されていればフロントエンド側に返却し
     * 画面上にユーザーのTwitterアカウント名やアバターを表示させる。
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
     * ユーザーがメールアドレスとパスワードでログインした際に、
     * 最新のTwitterアカウント情報を表示させるため、
     * ユーザーのTwitterアカウント情報とフォロー済みリストを更新するメソッド
     */
    public function updateTwitterUser()
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();
        // ログインユーザーのTwitterアカウント情報をDBから取得
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();

        // Twitterアカウント情報がある場合
        if ($twitter_user) {
            // ユーザーのTwitterアカウントでoAuth認証のコネクションを取得
            $connect = UsersTwitterOAuth::userOAuth($twitter_user);
            // Twitterアカウント情報取得用のパラメータを設定
            $params = array(
                'user_id' => $twitter_user->twitter_id,
                'include_entities' => false, //entitiesプロパティを取得するかどうか
            );
            // TwitterAPIでログインユーザーのTwitterアカウント情報を取得
            $result = $connect->get("users/show", $params);

            if ($result) {
                // TwitterAPIで取得したユーザー名、スクリーンネーム、アバターにDBとの差分があれば更新
                $twitter_user->user_name = $result->name;
                $twitter_user->screen_name = $result->screen_name;
                $twitter_user->twitter_avatar = $result->profile_image_url_https;
                $twitter_user->save();

                // ログインユーザーのTwitterフォロー済みユーザーリストを更新
                FollowListController::loginUsersFollowList();
            }
        }

        // Twitterアカウント情報を返却
        return $twitter_user;
    }

    /**
     * TwitterAPIの認証ページへリダイレクト処理を行うメソッド
     */
    public function redirectToProvider()
    {
        // ユーザーをOAuthプロバイダへ送る
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Twitter認証ページから戻ってきた後のログイン認証を行うメソッド
     */
    public function handleProviderCallback()
    {
        // Twitter認証ページからのコールバックURLにクエリパラメータ'denied'がある場合
        // （連携アプリの認証がキャンセルされた場合）
        if (isset($_GET['denied'])) {
            // セッションフラッシュにエラーメッセージを格納して仮想通貨アカウント一覧画面へリダイレクト
            return redirect('/twitter')->with(
                [
                    'message' => __('認証が取り消されました'),
                    'type' => 'danger',
                    'timeout' => 2000,
                ]
            );
        }
        try {
            // OAuthプロバイダでTwitter認証を行い、Twitterアカウント情報を取得
            $oauth_user = Socialite::driver('twitter')->user();
        } catch (Exception $e) {
            // エラーの場合はエラーメッセージを格納して仮想通貨アカウント一覧画面へリダイレクト
            return redirect('/twitter')->with(
                [
                    'message' => __('Twitterアカウントの連携を中断しました'),
                    'type' => 'danger',
                    'timeout' => 3000,
                ]
            );
        }

        // Twitterアカウント情報をDBに新規登録または更新する
        $twitterUser = $this->updateOrCreateTwitterUser($oauth_user);

        // Twitterアカウントが重複している場合は登録せずにリダイレクト
        if (!$twitterUser) {
            return redirect('/twitter')->with(
                [
                    'message' => __('そのTwitterアカウントはすでに登録されています'),
                    'type' => 'danger',
                    'timeout' => 3000,
                ]
            );
        }
        // 登録成功した場合は仮想通貨アカウント一覧画面へリダイレクトしてTwitterアカウント情報を再描画する
        return redirect('/twitter')->with(
            [
                'message' => __('Twitterアカウントを連携しました'),
                'type' => 'success',
                'timeout' => 2000,
            ]
        );
    }

    /**
     * ユーザーのTwitterアカウント情報がDBに保存されていれば取得し、
     * 登録されていなければ新規登録を行うメソッド
     */
    public function updateOrCreateTwitterUser($oauth_user)
    {
        // ログインユーザーのユーザーIDを取得
        $user_id = Auth::id();

        // ユーザーがTwitterAPIで認証を行ったTwitterIDがDBに保存済みであればレコードを1件取得
        $registeredUser = TwitterUser::where('twitter_id', $oauth_user->id)->first();
        // 同一のTwitterIDが登録済みで、かつログインユーザー以外のものである場合は登録しない
        if ($registeredUser && $registeredUser->user_id !== $user_id) {
            return '';
        }

        // ログインユーザーIDに紐づくTwitterユーザー情報があればDBの情報を更新し、なければ新規登録する
        $twitter_user = TwitterUser::updateOrCreate(
            ['user_id' => $user_id, 'twitter_id' => $oauth_user->id],
            [
                'twitter_id' => $oauth_user->id,
                'twitter_token' => $oauth_user->token,
                'twitter_token_secret' => $oauth_user->tokenSecret,
                'user_name' => $oauth_user->name,
                'screen_name' => $oauth_user->nickname,
                'twitter_avatar' => $oauth_user->user['profile_image_url_https'],
            ]
        );
        return $twitter_user;
    }

    /**
     * ユーザーがアカウント設定画面にて Twitterアカウント連携の解除をリクエストした際に
     * DBに登録されたTwitterアカウント情報を削除するメソッド
     */
    public function deleteTwitterUser()
    {
        // ログインユーザーに紐づくTwitterアカウント情報を取得
        $user_id = Auth::id();
        $twitter_user = TwitterUser::where('user_id', $user_id)->first();

        try {
            // ログインユーザーのTwitterアカウント情報が取得できた場合は、関連レコードを削除する
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
        } catch (Exception $e) {
            // エラーの場合はNotFoundエラーを返却
            return abort(404);
        }

        // ステータスコードを返却
        return response()->json();
    }
}
