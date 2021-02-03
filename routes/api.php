<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ユーザー登録API
Route::post('/register', 'Auth\RegisterController@register');
// ログインAPI
Route::post('/login', 'Auth\LoginController@login');
// ログアウトAPI
Route::post('/logout', 'Auth\LoginController@logout');

// パスワードリセットメール送信API
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// パスワードリセットフォーム
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// パスワードリセット
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// ログインユーザー情報を返却
Route::post('/user', function () {
  return Auth::user();
})->name('user');


// ログインユーザーのTwitter認証チェック（認証済みであればアカウント情報を返却）
Route::get('/auth/twitter/check', 'Auth\TwitterAuthController@checkTwitterUserAuth');

// トークンリフレッシュ
Route::get('/reflesh-token', function (Illuminate\Http\Request $request) {
  // 認証切れの場合はセッションのCSRFトークンをリフレッシュして返却
  $request->session()->regenerateToken();
  return response()->json();
});

// authミドルウェア、CORSミドルウェアを使用するルート
Route::group(['middleware' => ['auth', 'cors']], function () {
  // 関連ニュース取得API
  Route::get('/news', 'NewsController@index')->name('news.index');
  // トレンド一覧取得API
  Route::get('/trend', 'TrendController@index')->name('trend.index');
  // Twitterアカウント一覧取得API
  Route::get('/twitter', 'TwitterTargetListController@index')->name('twitter.index');
  // TwitterアカウントフォローAPI
  Route::post('/twitter/{id}/follow', 'FollowTargetController@createUsersFollow');
  // Twitterアカウントフォロー解除API
  Route::post('/twitter/{id}/unfollow', 'UnfollowTargetController@destroyUsersFollow');
  // 自動フォロー適用API
  Route::post('/autofollow/apply', 'AutoFollowController@applyAutoFollow');
  // 自動フォロー解除API
  Route::post('/autofollow/cancel', 'AutoFollowController@cancelAutoFollow');

  // Twitterアカウントの削除
  Route::post("/auth/twitter/delete", "Auth\TwitterAuthController@deleteTwitterUser");

  // パスワード変更
  Route::post('/password/change', 'Auth\ChangePasswordController@ChangePassword')->name('password.change');
  // ユーザー情報編集API
  Route::post('/account/change', 'Auth\ChangeAccountController@changeAccount')->name('account.change');

  // ユーザー退会API
  Route::post('/account/withdraw', 'Auth\ChangeAccountController@withdraw')->name('account.withdraw');
});
