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

// ユーザー登録処理API
Route::post('/register', 'Auth\RegisterController@register')->name('register');
// ログイン処理API
Route::post('/login', 'Auth\LoginController@login')->name('login');
// ログアウト処理API
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

// パスワードリセットメール送信API
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// パスワードリセット処理API
Route::post('/password/update', 'Auth\ResetPasswordController@reset')->name('password.update');

// お問い合わせフォーム入力内容確認API
Route::post('/contact/confirm', 'ContactController@confirm')->name('contact.confirm');
//お問い合わせフォーム送信API
Route::post('/contact/send', 'ContactController@send')->name('contact.send');

// ログインユーザー情報の返却API
Route::post('/user', function () {
  return Auth::user();
})->name('user');

// ログインユーザーのTwitter認証チェックAPI（認証済みであればDBからアカウント情報を返却）
Route::post('/auth/twitter/check', 'Auth\TwitterAuthController@checkTwitterUserAuth');

// トークンリフレッシュAPI
Route::get('/refresh-token', function (Illuminate\Http\Request $request) {
  // 認証切れの場合はセッションのCSRFトークンをリフレッシュして返却
  $request->session()->regenerateToken();
  return response()->json();
});

// authミドルウェア、CORSミドルウェアを使用するルート
Route::group(['middleware' => ['auth', 'cors']], function () {
  // ログインユーザーのTwitterアカウント情報更新API
  Route::post('/auth/twitter/update', 'Auth\TwitterAuthController@updateTwitterUser');

  // ホーム画面の最新データ表示API
  Route::get('/news/latest', 'NewsController@showLatest');
  Route::get('/trend/latest', 'TrendController@showLatest');
  Route::get('/twitter/latest', 'TwitterTargetListController@showLatest');


  // 関連ニュース取得API
  Route::get('/news', 'NewsController@index')->name('news.index');
  // トレンド一覧取得API
  Route::get('/trend', 'TrendController@index')->name('trend.index');
  // 仮想通貨アカウント一覧取得API
  Route::get('/twitter', 'TwitterTargetListController@index')->name('twitter.index');
  // 仮想通貨アカウントフォローAPI
  Route::post('/twitter/{id}/follow', 'FollowTargetController@createUsersFollow');
  // 仮想通貨アカウントフォロー解除API
  Route::post('/twitter/{id}/unfollow', 'UnfollowTargetController@destroyUsersFollow');
  // 自動フォロー適用API
  Route::post('/autofollow/apply', 'AutoFollowController@applyAutoFollow');
  // 自動フォロー解除API
  Route::post('/autofollow/cancel', 'AutoFollowController@cancelAutoFollow');
  // 自動フォロー履歴取得API
  Route::get('/autofollow/list', 'AutoFollowController@showAutoFollowList');

  // ユーザーのTwitterアカウント登録削除API
  Route::post("/auth/twitter/delete", "Auth\TwitterAuthController@deleteTwitterUser");

  // パスワード変更API
  Route::post('/password/change', 'Auth\ChangePasswordController@ChangePassword')->name('password.change');
  // ユーザー情報編集API
  Route::post('/account/change', 'Auth\EditAccountController@EditAccount')->name('account.change');
  // ユーザーアカウント削除・退会API
  Route::post('/account/withdraw', 'Auth\EditAccountController@withdrawAccount')->name('account.withdraw');
});
