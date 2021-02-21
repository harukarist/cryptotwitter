<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Auth\UsersTwitterOAuth;

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

    // ログインユーザーとターゲットとの関係性を取得するメソッドを実行
    $result = FollowTargetController::fetchFriendship($twitter_id, $target_id, $connect);

    // 関係性を取得できなかった場合はエラーを返却
    if (!$result || !property_exists($result, 'relationship')) {
      return abort(404);
    }

    // TwitterAPIからの返却値に'relationship'プロパティがある場合、フォロー状況の値を取得
    $following = $result->relationship->source->following; //自分が相手をフォローしていたらtrue

    // フォローしていない場合は何もせずに返却
    if (!$following) {
      return [
        'message' => 'このアカウントをフォローしていません',
        'target_id' => $target_id
      ];
    }
    // ターゲットをフォロー解除するメソッドを実行して結果を返却
    self::unfollowTarget($twitter_user, $target_id, $connect);
    return [
      'message' => 'アカウントをフォロー解除しました',
      'target_id' => $target_id
    ];
  }

  /**
   * ターゲットをフォロー解除するメソッド
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
