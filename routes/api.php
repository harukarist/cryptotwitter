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

// ユーザー登録
Route::post('/register', 'Auth\RegisterController@register')->name('register');
// ログイン
Route::post('/login', 'Auth\LoginController@login')->name('login');
// ログアウト
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
// ログインユーザー情報を返却
Route::get('/user', function () {
  return Auth::user();
})->name('user');

// ログインユーザーのTwitter認証チェック（認証済みであればアカウント情報を返却）
Route::get('/auth/twitter/check', 'Auth\TwitterAuthController@checkTwitterUserAuth');

// トークンリフレッシュ
Route::get('/reflesh-token', function (Illuminate\Http\Request $request) {
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
  // Twitterアカウントフォロー処理
  Route::post('/twitter/{id}/follow', 'FollowTargetController@createUsersFollow');
  // Twitterアカウントフォロー解除処理
  Route::post('/twitter/{id}/unfollow', 'UnfollowTargetController@destroyUsersFollow');

  // 自動フォローを適用
  Route::get('/autofollow/apply', 'AutofollowController@applyAutoFollow');
  // 自動フォローを解除
  Route::get('/autofollow/cancel', 'AutofollowController@cancelAutoFollow');

  // ティッカー情報取得（管理者用）
  Route::get('/tickers', 'TickerController@index');
});
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
