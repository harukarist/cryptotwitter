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

Route::group(['middleware' => 'auth'], function () {
  Route::get('/news', 'NewsController@index');
  Route::get('/trend', 'TrendController@index');
  Route::get('/tickers', 'TickerController@index');

  // Twitterログイン認証
  Route::get('/auth/twitter', 'Auth\TwitterAuthController@redirectToProvider');
  Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
  Route::get("/auth/twitter/logout", "Auth\TwitterAuthController@logout");
});
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
