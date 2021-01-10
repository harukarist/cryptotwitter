<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/getTweet', 'FetchTweetController@fetchAllTweets');
Route::get('/getUser', 'FetchTwitterUserController@fetchUsers');
Route::get('/twpro', 'FetchTwproController@fetchUsers');
Route::get('/countTweet', 'CountTweetController@countTweet');

// // 会員登録・ログイン・ログアウト・パスワード再設定
// Auth::routes();
Route::group(['middleware' => 'auth'], function () {
    // Twitterログイン認証
    Route::get('/auth/twitter/', 'Auth\TwitterAuthController@redirectToProvider');
    Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
    Route::get("/auth/twitter/delete", "Auth\TwitterAuthController@delete");
});

// 初回アクセス時のみLaravel側でapp.blade.phpを表示し、
// 以後はフロント側のVueRouterでルーティングを行う
// {any?} で任意のパスパラメータ any を受け入れ
// パスパラメータの文字列は任意'.+'
Route::get('/{any?}', function () {
    return view('layouts.app');
})->where('any', '.+');
