<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Auth\UsersTwitterOAuth;

class FollowTargetController extends Controller
{
  /**
   * WebAPからのリクエストを元に、ログインユーザーのTwitterアカウントで
   * ターゲットをフォローする処理
   */
  public function createUsersFollow(string $target_id)
  {
    // ログインユーザーのTwitterアカウント情報を取得
    $twitter_user = Auth::user()->twitter_user;

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$twitter_user) {
      return abort(404);
    }

    // ログインユーザーとターゲットのTwitterIDを指定してターゲットをフォローするメソッドを実行
    $follow = self::createFollow($twitter_user, $target_id);
    return $follow;
  }

  /**
   * ログインユーザーとターゲットのTwitterを
   * フォローする処理
   */
  static public function createFollow(object $twitter_user, string $target_id)
  {
    // ログインユーザーのTwitterIDを取得
    $twitter_id = $twitter_user->twitter_id;

    // ユーザーのTwitterアカウントでoAuth認証するメソッドを実行
    $connect = UsersTwitterOAuth::userOAuth($twitter_user);

    // ターゲットをフォロー済みかどうかをチェックするメソッドを実行
    $is_following = self::checkIsFollowing($twitter_id, $target_id, $connect);

    // すでにターゲットをフォロー済みの場合は何もせずに返却
    if ($is_following) {
      return [
        'message' => 'アカウントはフォロー済みです',
        'target_id' => $target_id,
        'do_follow' => false,
      ];
    }

    // ターゲットをまだフォローしていない場合は、ターゲットをフォローするメソッドを実行
    self::followTarget($twitter_user, $target_id, $connect);

    return [
      'message' => 'アカウントをフォローしました',
      'target_id' => $target_id,
      'do_follow' => true,
    ];
  }

  /**
   * 対象Twitterアカウントのフォロー状況をチェックするメソッド
   */
  static public function checkIsFollowing($twitter_id, $target_id, $connect)
  {
    // ユーザーのTwitterAPIレートリミットをチェック
    $CATEGORY = "friendships";
    $ENDPOINT = "/friendships/show";
    $limit = UsersTwitterOAuth::checkLimit($connect, $CATEGORY, $ENDPOINT);

    // フォロー状況チェック/friendships/show のレートリミットが上限に達していたら処理を終了
    if (!$limit) {
      logger()->info("フォロー状況チェックのリクエスト上限に達しました");
      return;
    }

    // ユーザーのTwitterIDとフォロー対象のTwitterIDをTwitterAPIのパラメータに指定
    $params = array(
      'source_id' => $twitter_id,
      'target_id' => $target_id,
    );

    // TwitterAPIでフォロー状況を取得
    $result = $connect->get($ENDPOINT, $params);

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }
    // TwitterAPIからの返却値に'relationship'プロパティがある場合
    if (property_exists($result, 'relationship')) {
      // フォロー状況の値を返却
      $is_following = $result->relationship->source->following;
      return $is_following;
    }
  }

  /**
   * ターゲットをフォローするメソッド
   */
  static public function followTarget($twitter_user, $target_id, $connect)
  {

    $params = array(
      'user_id' => $target_id,
      'follow' => true, //フォローを相手に通知するか
    );
    $ENDPOINT = "friendships/create";
    // レートリミットは1日あたり400件

    // TwitterAPIでターゲットをフォロー
    $result = $connect->post($ENDPOINT, $params);

    // 取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }
    // target_usersテーブルから該当TwitterIDのレコードを取得
    $target = TargetUser::where('twitter_id', $target_id)->first();
    // followsテーブルに登録済みであれば一旦削除
    $twitter_user->follows()->detach($target->id);
    // followsテーブルにユーザーとフォロー相手のidを登録
    $twitter_user->follows()->attach($target->id);

    return $result;
  }
}
