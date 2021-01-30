<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;

class UnfollowTargetController extends Controller
{
  /**
   * ログインユーザーのTwitterアカウントでターゲット1件をフォロー解除
   */
  public function destroyUsersFollow(string $target_id)
  {
    // ログインユーザーのTwitterアカウント情報を取得
    $twitter_user = Auth::user()->twitter_user;

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$twitter_user) {
      return abort(404);
    }
    $unfollow = self::destroyFollow($twitter_user, $target_id);
    return $unfollow;
  }

  /**
   * ターゲット1件をフォロー解除
   */
  static public function destroyFollow(object $twitter_user, string $target_id)
  {
    // ログインユーザーのTwitterIDを取得
    $twitter_id = $twitter_user->twitter_id;

    // ユーザーのTwitterアカウントでoAuth認証
    $connect = UsersTwitterOAuth::userOAuth($twitter_user);

    // ターゲットをフォロー済みかどうかをチェック
    $is_following = FollowTargetController::checkIsFollowing($twitter_id, $target_id, $connect);

    // フォローしていない場合は何もせずに返却
    if (!$is_following) {
      return [
        'message' => 'このアカウントをフォローしていません',
        'target_id' => $target_id
      ];
    }

    // ターゲットをフォロー解除
    self::unfollowTarget($twitter_user, $target_id, $connect);

    // return redirect('/twitter')->with('flash_message', __('アカウントをフォローしました'));
    return [
      'message' => 'アカウントをフォロー解除しました',
      'target_id' => $target_id
    ];
  }

  /**
   * ターゲットをフォロー解除
   */
  static public function unfollowTarget($twitter_user, $target_id, $connect)
  {

    $params = array(
      'user_id' => $target_id,
    );
    $endpoint = "friendships/destroy";
    // レートリミット上限なし

    // TwitterAPIでターゲットをフォロー解除
    $result = $connect->post($endpoint, $params);

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }

    // target_usersテーブルから該当TwitterIDのレコードを取得
    $target = TargetUser::where('twitter_id', $target_id)->first();
    // followsテーブルからユーザーとフォロー相手のidを削除
    $twitter_user->follows()->detach($target->id);

    return $result;
  }
}
