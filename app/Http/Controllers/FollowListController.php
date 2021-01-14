<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;

class FollowListController extends Controller
{
  /**
   * ログインユーザーのフォロー一覧を作成
   */

  static public function loginUsersFollowList()
  {
    $twitter_user = Auth::user()->twitter_user;
    if (!$twitter_user) {
      return;
    }
    self::createOrUpdateFollowList($twitter_user);
    return;
  }

  // ユーザーTwitterアカウントのフォロー済み一覧リストをフォローテーブルに保存
  static public function createOrUpdateFollowList($twitter_user)
  {
    // ユーザーのTwitterアカウントでoAuth認証
    $connect = UsersTwitterOAuth::userOAuth($twitter_user);

    // ログインユーザーのフォロー済みID一覧をTwitterAPIから取得
    $twitter_id = $twitter_user->twitter_id;
    $follows = self::fetchFollowIds($twitter_id, $connect);

    // フォロー済みIDが取得できた場合
    if ($follows) {
      // ログインユーザーに紐づくfollowsテーブルのレコードを一旦削除
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
    }

    return;
  }

  /**
   * フォロー済みID一覧をTwitterAPIから取得
   */
  static public function fetchFollowIds($twitter_id, $connect)
  {
    $cursor = '-1'; //初期値は-1
    $follows = [];
    $category = "friends";
    $endpoint = "/friends/ids";

    // ユーザーアカウントでのTwitterAPIのレートリミットをチェック
    $limit = UsersTwitterOAuth::checkLimit($connect, $category, $endpoint);

    if (!$limit) {
      logger()->info("フォロー済みID取得のリクエスト上限に達しました");
      return;
    }

    while ($cursor !== 0) {
      $params = array(
        'user_id' => $twitter_id,
        'cursor' => $cursor,
        'stringify_ids' => true, //フォローIDの末尾が丸められないよう、文字列として取得
        'count' => 5000 //上限は5000
      );

      // 対象ユーザーがフォローしているユーザーをTwitterIDの一覧で取得
      $result = $connect->get($endpoint, $params);

      if (property_exists($result, 'ids')) {
        $follows = array_merge($follows, $result->ids);
        $cursor = $result->next_cursor;
      } else {
        $cursor = 0;
      }
    }
    $count = count($follows);
    logger()->info("フォロー済み一覧を{$count}件取得");

    return $follows;
  }
}
