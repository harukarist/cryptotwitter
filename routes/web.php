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

// Auth::routes();
// Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// パスワード再設定フォーム
Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');

// バッチ処理の手動実行用
Route::get('/weekly', 'FetchTweetController@fetchWeeklyTweets');
Route::get('/newTweet', 'FetchTweetController@fetchLatestTweets');
Route::get('/getUser', 'FetchTargetController@fetchUsers');
Route::get('/latest', 'FetchTargetTweetController@fetchLatestTweet');
Route::get('/getNews', 'FetchNewsController@fetchNews');
Route::get('/getTwpro', 'FetchTwproController@fetchUsers');
Route::get('/addUser', 'LookupTwitterUserController@addUsers');
Route::get('/countTweet', 'CountTweetController@countTweet');
Route::get('/followList', 'FollowListController@loginUsersFollowList');
Route::get('/autofollow', 'FetchAutoFollowController@autoFollow');
Route::get('/deleteTweets', 'DeleteOldRecordsController@deleteTweets');
Route::get('/deleteLogs', 'DeleteOldRecordsController@deleteFetchTweetsLogs');

Route::group(['middleware' => 'auth'], function () {
    // Twitterログイン認証（TwitterAPIへのリダイレクト）
    Route::get('/auth/twitter/login', 'Auth\TwitterAuthController@redirectToProvider');
    // Twitterログイン認証（TwitterAPIからのコールバック）
    Route::get('/auth/twitter/callback', 'Auth\TwitterAuthController@handleProviderCallback');
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
