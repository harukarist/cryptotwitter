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

// バッチ処理の手動実行用
Route::get('/getTweet', 'FetchTweetController@fetchAllTweets');
Route::get('/getUser', 'FetchTwitterUserController@fetchUsers');
Route::get('/getNews', 'FetchNewsController@fetchNews');
Route::get('/getTwpro', 'FetchTwproController@fetchUsers');
Route::get('/addUser', 'LookupTwitterUserController@addUsers');
Route::get('/countTweet', 'CountTweetController@countTweet');
Route::get('/followList', 'FollowListController@loginUsersFollowList');
Route::get('/autofollow', 'AutoFollowController@autoFollow');
Route::get('/twitter/{id}/follow', 'FollowTargetController@createFollow');
Route::get('/twitter/{id}/unfollow', 'FollowTargetController@destroyFollow');



Route::group(['middleware' => 'auth'], function () {
    // Twitterログイン認証（TwitterAPIへのリダイレクト）
    Route::get('/auth/twitter/login', 'Auth\TwitterAuthController@redirectToProvider');
    // Twitterログイン認証（TwitterAPIからのコールバック）
    Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
    // Twitterアカウントの削除
    Route::get("/auth/twitter/delete", "Auth\TwitterAuthController@delete");
});

// 初回アクセス時のみLaravel側でapp.blade.phpを表示し、
// 以後はフロント側のVueRouterでルーティングを行う
// {any?} で任意のパスパラメータ any を受け入れ
// パスパラメータの文字列は任意'.+'
Route::middleware(['cors'])->group(function () {
    Route::get('/{any?}', function () {
        return view('layouts.app');
    })->where('any', '.+');
});

// // 会員登録・ログイン・ログアウト・パスワード再設定
// Auth::routes();
