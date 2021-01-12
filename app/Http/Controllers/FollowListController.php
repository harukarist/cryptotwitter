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
  static public function createOrUpdateFollowList()
  {
    $twitter_user = Auth::user()->twitter_user;
    if (!$twitter_user) {
      return;
    }
    $twitter_id = $twitter_user->twitter_id;
    // dd($twitter_id);

    // ログインユーザーのフォロー済みID一覧を取得
    $follows = self::fetchFollowIds($twitter_id);
    // echo "フォロー済み一覧を取得<br>";
    logger()->info("フォロー済み一覧を取得");

    // ログインユーザーに紐づくfollowsテーブルのレコードを一旦削除
    DB::table('follows')->where('twitter_user_id', $twitter_user->id)->delete();

    foreach ($follows as $follow_id) {
      // target_usersテーブルに該当TwitterIDのレコードがあれば取得
      $target = TargetUser::where('twitter_id', $follow_id)->first();
      // 該当TwitterIDのレコードがある（CryptoTrendで扱う仮想通貨アカウントに該当する）場合
      if ($target) {
        // followsテーブルに登録済みであれば一旦削除
        $twitter_user->follows()->detach($target->id);
        // followsテーブルにユーザーとフォロー相手のidを登録
        $twitter_user->follows()->attach($target->id);
        // echo "{$target->user_name}をフォローテーブルに追加<br>";
        logger()->info("{$target->user_name}をフォローテーブルに追加");
      } else {
        // echo "{$follow_id}は対象外<br>";
        logger()->info("{$follow_id}は対象外");
      }
    }
    return;
  }

  /**
   * フォロー済みID一覧をTwitterAPIから取得
   */
  static public function fetchFollowIds($twitter_id)
  {
    $cursor = '-1'; //初期値は-1
    $follows = [];

    while ($cursor !== 0) {
      $params = array(
        'user_id' => $twitter_id,
        'cursor' => $cursor,
        'stringify_ids' => true, //フォローIDの末尾が丸められないよう、文字列として取得
        'count' => 5000 //上限は5000
      );

      $result = \Twitter::get("friends/ids", $params);
      if (property_exists($result, 'ids')) {
        $follows = array_merge($follows, $result->ids);
        $cursor = $result->next_cursor;
      } else {
        $cursor = 0;
      }
    }
    return $follows;
  }
}
