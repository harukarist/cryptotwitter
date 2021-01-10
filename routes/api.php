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

// CORS
Route::middleware(['cors'])->group(function () {
  // Preflightリクエストの場合
  Route::options('accounts', function () {
    return response()->json();
  });
  // Twitterログイン認証
  Route::get('/auth/twitter/', 'Auth\TwitterAuthController@redirectToProvider')->name('oauth.twitter');
  Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
  Route::get("/auth/twitter/logout", "Auth\TwitterAuthController@logout")->name('oauth.logout');
});


Route::group(['middleware' => 'auth'], function () {

  // 関連ニュース取得API
  Route::get('/news', 'NewsController@index')->name('news.index');
  // トレンド一覧取得API
  Route::get('/trend', 'TrendController@index')->name('trend.index');
  // Twitterアカウント一覧取得API
  Route::get('/twitter', 'TwitterListController@index')->name('twitter.index');

  Route::get('/auth/twitter/check', 'Auth\TwitterAuthController@checkTwitterUserAuth');
  Route::get('/auth/twitter/reset', 'Auth\TwitterAuthController@resetTwitterUserAuth');
  // ティッカー情報取得（管理者用）
  Route::get('/tickers', 'TickerController@index');
});
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
