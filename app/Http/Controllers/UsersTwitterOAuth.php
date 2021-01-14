<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;

class UsersTwitterOAuth
{
  /**
   * ログインユーザーのトークンでOAuth認証
   */
  static public function userOAuth($twitter_user)
  {
    $user_token = $twitter_user->twitter_token;
    $user_secret = $twitter_user->twitter_token_secret;

    // ヘルパー関数config()でconfig/twitter.phpを参照してインスタンスを作成
    $config = config('twitter');
    //接続に必要な接続インスタンスを生成
    $connect = new TwitterOAuth(
      $config['api_key'],
      $config['secret_key'],
      $user_token,
      $user_secret,
    );
    // dd($connect);
    // dd($config['api_key']);
    return $connect;
  }

  /**
   * ログインユーザーのTwitterAPIレートリミットを取得
   */
  static public function checkLimit($connect, $category, $endpoint)
  {
    // ログインユーザーのTwitterAPIレートリミットを取得
    $result = $connect->get("application/rate_limit_status");
    // dd($result);

    if (!$result) {
      return abort(404);
    }
    // APIから返ってきたオブジェクトにエラープロパティがあれば残り回数を0にする
    if (property_exists($result, 'errors')) {
      logger()->info("残り使用可能回数が取得できませんでした");
      return 0;
    }
    // 検索APIの残り使用可能回数が存在する場合は回数の値を取得
    if (property_exists($result, 'resources')) {
      $resources_obj = $result->resources;

      if (property_exists($resources_obj, $category)) {
        $remain_count = $resources_obj->$category->$endpoint->remaining; // 残り使用回数

        logger()->info("{$endpoint}へのリクエストは残り{$remain_count}回");

        return $remain_count;
      }
    }
  }
}
