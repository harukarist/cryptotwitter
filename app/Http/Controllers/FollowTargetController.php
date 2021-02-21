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
    // ユーザーのTwitterアカウントでoAuth認証するメソッドを実行
    $connect = UsersTwitterOAuth::userOAuth($twitter_user);

    // ログインユーザーとターゲットのTwitterIDを指定してターゲットをフォローするメソッドを実行
    $follow = self::createFollow($twitter_user, $target_id, $connect);
    return $follow;
  }

  /**
   * ログインユーザーのTwitterアカウントでターゲットのTwitterアカウントをフォローする処理
   */
  static public function createFollow(object $twitter_user, string $target_id, $connect)
  {
    // ログインユーザーのTwitterIDを取得
    $twitter_id = $twitter_user->twitter_id;

    // ログインユーザーとターゲットとの関係性を取得するメソッドを実行
    $result = self::fetchFriendship($twitter_id, $target_id, $connect);

    // 関係性を取得できなかった場合はエラーを返却
    if (!$result || !property_exists($result, 'relationship')) {
      return [
        'message' => 'アカウントをフォローできませんでした',
        'target_id' => $target_id,
        'do_follow' => false,
      ];
    }

    // TwitterAPIからの返却値に'relationship'プロパティがある場合、フォロー状況の値を取得
    $following = $result->relationship->source->following; //自分が相手をフォローしていたらtrue
    $blocking = $result->relationship->source->blocking; //自分が相手をブロックしていたらtrue
    $blocked = $result->relationship->source->blocked_by; //自分が相手にブロックされていたらtrue
    $muted = $result->relationship->source->muting; //自分が相手をミュートしていたらtrue
    $requested = $result->relationship->source->following_requested; //自分が相手にフォローリクエスト済みならtrue

    // すでにターゲットをフォロー済みの場合は何もせずに返却
    if ($following) {
      return [
        'message' => 'アカウントはフォロー済みです',
        'target_id' => $target_id,
        'do_follow' => false,
      ];
    }
    // ブロック、ミュート、フォローリクエスト済みアカウントの場合は何もせずに返却
    if ($blocking || $blocked || $muted || $requested) {
      return [
        'message' => 'フォローできないアカウントです',
        'target_id' => $target_id,
        'do_follow' => false,
      ];
    }
    // その他の場合は、ターゲットをフォローするメソッドを実行して結果を返却
    self::followTarget($twitter_user, $target_id, $connect);
    return [
      'message' => 'アカウントをフォローしました',
      'target_id' => $target_id,
      'do_follow' => true,
    ];
  }

  /**
   * ユーザーのTwitterアカウントとターゲットアカウントの関係性を取得するメソッド
   */
  static public function fetchFriendship($twitter_id, $target_id, $connect)
  {
    // ユーザーのTwitterAPIレートリミットをチェック(上限は 180回/15min)
    $CATEGORY = "friendships";
    $ENDPOINT = "/friendships/show";
    $limit = UsersTwitterOAuth::checkLimit($connect, $CATEGORY, $ENDPOINT);

    // フォロー状況チェック/friendships/show のレートリミットが上限に達していたら処理を終了
    if (!$limit) {
      logger()->info("フォロー状況チェックのリクエスト上限に達しました");
      return false;
    }

    // ユーザーのTwitterIDとフォロー対象のTwitterIDをTwitterAPIのパラメータに指定
    $params = array(
      'user_id' => $target_id,
    );
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

    return $result;
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
