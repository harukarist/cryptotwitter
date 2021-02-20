<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Auth\UsersTwitterOAuth;


/**
 * ログインユーザーTwitterアカウントのフォロー済み一覧を
 * 管理するクラス
 * 他のコントローラーから呼び出して使用する
 */
class FollowListController extends Controller
{
  /**
   * ログインユーザーのフォロー一覧を更新
   */
  static public function loginUsersFollowList()
  {
    // ログインユーザーに紐づくTwitterアカウント情報を取得
    $twitter_user = Auth::user()->twitter_user;
    // Twitterアカウント情報が取得できない場合は何もせず返却
    if (!$twitter_user) {
      return;
    }
    // Twitterアカウント情報があればフォロー済み一覧リストを更新
    self::createOrUpdateFollowList($twitter_user);
    return;
  }

  /**
   * ユーザーTwitterアカウントのフォロー済み一覧リストをフォローテーブルに保存
   */
  static public function createOrUpdateFollowList($twitter_user)
  {
    // ユーザーのTwitterアカウントでoAuth認証
    $connect = UsersTwitterOAuth::userOAuth($twitter_user);

    // DBに保存されたTwitterアカウント情報のTwitterIDを格納
    $twitter_id = $twitter_user->twitter_id;
    // ログインユーザーのフォロー済みID一覧をTwitterAPIから取得
    $follows = self::fetchFollowIds($twitter_id, $connect);

    // フォロー済みIDが取得できなかった場合は処理を終了
    if (!$follows) {
      return false;
    }

    // フォロー済みIDが取得できた場合はログインユーザーに紐づく保存済みのfollowsテーブルのレコードを一旦削除
    DB::table('follows')->where('twitter_user_id', $twitter_user->id)->delete();

    $count = 0;
    // ユーザーのフォロー済みIDのうち、CryptoTrendで扱う仮想通貨アカウントのみ処理
    foreach ($follows as $follow_id) {
      // target_usersテーブルに該当TwitterIDのレコードがあれば取得
      $target = TargetUser::where('twitter_id', $follow_id)->first();
      // 該当TwitterIDのレコードがある場合
      if ($target) {
        // followsテーブルにユーザーとフォロー相手のidを登録
        $twitter_user->follows()->attach($target->id);
        $count++;
      }
    }
    logger()->info("{$twitter_user->user_name}さんのフォローテーブルを{$count}件更新");

    return;
  }

  /**
   * フォロー済みID一覧をTwitterAPIから取得
   */
  static public function fetchFollowIds($twitter_id, $connect)
  {
    $cursor = '-1'; //初期値は-1
    $follows = []; //TwitterAPIから取得したフォロー済みIDを格納する配列
    $CATEGORY = "friends";
    $ENDPOINT = "/friends/ids";

    // ユーザーアカウントでのTwitterAPIのレートリミットをチェック
    $limit = UsersTwitterOAuth::checkLimit($connect, $CATEGORY, $ENDPOINT);

    // リミット上限に達した場合はエラーログを出力
    if (!$limit) {
      logger()->info("フォロー済みID取得のリクエスト上限に達しました");
      return false;
    }

    // リミット残り回数がある場合はTwitterAPIから返却されるカーソルが0になるまで
    // 対象ユーザーのフォローユーザーを取得
    while ($cursor !== 0) {
      $params = array(
        'user_id' => $twitter_id,
        'cursor' => $cursor,
        'stringify_ids' => true, //フォローIDの末尾が丸められないよう、文字列として取得
        'count' => 5000 //上限は5000
      );

      // 対象ユーザーがフォローしているユーザーをTwitterIDの一覧で取得
      $result = $connect->get($ENDPOINT, $params);

      // フォローユーザーIDが返却された場合
      if (property_exists($result, 'ids')) {
        // TwitterAPIから取得したフォロー済みIDを配列に格納
        $follows = array_merge($follows, $result->ids);
        // 次の取得用カーソルを変数に格納
        $cursor = $result->next_cursor;
      } else {
        // フォローユーザーIDが返却されなかった場合はカーソルを0にしてループを終了
        $cursor = 0;
      }
    }
    $count = count($follows);
    logger()->info("フォロー済み一覧を{$count}件取得");

    return $follows;
  }
}
