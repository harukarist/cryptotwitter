<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Auth\UsersTwitterOAuth;

/**
 * フロント側からのリクエストを元に、ログインユーザーのTwitterアカウントで
 * 指定された仮想通貨アカウント（ターゲット）1件をフォロー解除するクラス
 */
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
    // ログインユーザーとターゲットのTwitterIDを引数に指定して
    // ターゲットをフォロー解除するメソッドを実行
    $result = self::destroyFollow($twitter_user, $target_id);
    // 結果をVue側に返却する
    return $result;
  }

  /**
   * ユーザーのTwitterアカウントとターゲットの仮想通貨アカウントとの関係に応じた処理を行うメソッド
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
        'target_id' => $target_id,
        'is_done' => false,
      ];
    }
    // ターゲットをフォロー解除するメソッドを実行して結果を返却
    self::unfollowTarget($twitter_user, $target_id, $connect);
    return [
      'message' => 'アカウントをフォロー解除しました',
      'target_id' => $target_id,
      'is_done' => true,
    ];
  }

  /**
   * ターゲットの仮想通貨アカウント1件をTwitterAPIでフォロー解除するメソッド
   */
  static public function unfollowTarget($twitter_user, $target_id, $connect)
  {
    // ターゲットのTwitterIDをTwitterAPIのパラメータに指定
    $params = array(
      'user_id' => $target_id,
    );
    // アカウントフォロー解除のエンドポイントを指定
    $endpoint = "friendships/destroy";
    // フォロー解除の上限は規定されていないため、レートリミットチェックは行わない

    // エンドポイントとパラメータを指定して、TwitterAPIでターゲットをフォロー解除
    $result = $connect->post($endpoint, $params);

    // 結果が取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }

    // target_usersテーブルから該当ターゲットのTwitterIDを指定してレコードを取得
    $target = TargetUser::where('twitter_id', $target_id)->first();
    // followsテーブルからユーザーと該当ターゲットのidを削除し、未フォロー状態とする
    $twitter_user->follows()->detach($target->id);
    // 結果を呼び出し元のメソッドに返却
    return $result;
  }
}
