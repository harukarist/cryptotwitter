<?php

namespace App\Http\Controllers\Auth;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * TwitterAPIによるログインユーザーのTwitterアカウント認証、及び
 * レートリミット取得を行う処理をまとめた共通クラス
 */
class UsersTwitterOAuth
{
  /**
   * DBに保存されたログインユーザーのTwitterアカウント情報を引数で受け取り、
   * OAuth認証を行うメソッド
   */
  static public function userOAuth($twitter_user)
  {
    // 引数で受け取ったTwitterアカウント情報のうち、トークン・シークレットキーを変数に格納
    $user_token = $twitter_user->twitter_token;
    $user_secret = $twitter_user->twitter_token_secret;

    // ヘルパー関数config()でconfig/twitter.phpを参照し、OAuth認証用の設定を読み込み
    $config = config('twitter');
    // Abraham\TwitterOAuthの接続インスタンスを生成して返却する
    $connect = new TwitterOAuth(
      $config['api_key'], //当アプリのTwitterAPIキー
      $config['secret_key'], //当アプリのTwitterAPIシークレットキー
      $user_token, //ユーザーのTwitterトークン
      $user_secret, //ユーザーのTwitterシークレットキー
    );
    return $connect;
  }

  /**
   * ログインユーザーのTwitterAPIレートリミットを取得するメソッド。
   * ユーザーのTwitterAPIへの認証情報、カテゴリー（"friends"など）、エンドポイント（"/friends/ids"など）を
   * 引数で受け取り、そのエンドポイントへのリクエストを実行できる残り回数をTwitterAPIに問い合わせて
   * 結果を返却する。
   */
  static public function checkLimit($connect, $category, $endpoint)
  {
    // TwitterAPIの"application/rate_limit_status"を利用して、
    // ユーザーのTwitterアカウントでのレートリミット情報を取得して変数に格納
    $result = $connect->get("application/rate_limit_status");

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }
    // TwitterAPIから返却されたオブジェクトにエラープロパティがある場合は、残り回数として0を返却
    if (property_exists($result, 'errors')) {
      logger()->info("残り使用可能回数が取得できませんでした");
      return 0;
    }
    // TwitterAPIから返却されたオブジェクトに'resources'プロパティがある場合は、残り使用可能回数を返却
    if (property_exists($result, 'resources')) {
      // 'resources'プロパティの中に、引数で受け取ったカテゴリーのプロパティがある場合
      $resources_obj = $result->resources;
      if (property_exists($resources_obj, $category)) {
        // 引数で受け取ったカテゴリー及びエンドポイントの残り使用回数を変数に格納して返却
        $remain_count = $resources_obj->$category->$endpoint->remaining; // 残り使用回数
        logger()->info("{$endpoint}へのリクエストは残り{$remain_count}回");
        return $remain_count;
      }
    }
  }
}
