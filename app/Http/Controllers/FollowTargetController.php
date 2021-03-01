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
 * 指定された仮想通貨アカウント（ターゲット）1件をフォローするクラス
 */
class FollowTargetController extends Controller
{
  /**
   * フロント側からターゲットのTwitterIDを受け取り、
   * ログインユーザーのTwitterアカウントでoAuth認証を行った後、
   * ターゲットをフォローするメソッドを実行し、結果をフロント側に返却するメソッド
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

    // ログインユーザーとターゲットのTwitterID、ユーザーのoAuth認証結果を引数に指定して
    // ターゲットをフォローするメソッドを実行
    $result = self::createFollow($twitter_user, $target_id, $connect);
    // 結果をVue側に返却する
    return $result;
  }

  /**
   * ユーザーのTwitterアカウントとターゲットの仮想通貨アカウントとの関係に応じた処理を行うメソッド
   * (フロント側からのリクエストによるフォローの他、バッチ処理による自動フォローでもこのメソッドを使用する)
   */
  static public function createFollow(object $twitter_user, string $target_id, $connect)
  {
    // ユーザーのTwitterIDを取得
    $twitter_id = $twitter_user->twitter_id;

    // ユーザーとターゲットとの関係性を取得するメソッドを実行
    $result = self::fetchFriendship($twitter_id, $target_id, $connect);

    // 関係性を取得できなかった場合はエラーを返却
    if (!$result || !property_exists($result, 'relationship')) {
      return [
        'message' => 'アカウントをフォローできませんでした',
        'target_id' => $target_id,
        'is_done' => false,
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
        'is_done' => false,
      ];
    }
    // ブロック、ミュート、フォローリクエスト済みアカウントの場合は何もせずに返却
    if ($blocking || $blocked || $muted || $requested) {
      return [
        'message' => 'フォローできないアカウントです',
        'target_id' => $target_id,
        'is_done' => false,
      ];
    }
    // その他の場合は、ターゲットをフォローするメソッドを実行して結果を返却
    self::followTarget($twitter_user, $target_id, $connect);
    return [
      'message' => 'アカウントをフォローしました',
      'target_id' => $target_id,
      'is_done' => true,
    ];
  }

  /**
   * ユーザーのTwitterアカウントとターゲットの仮想通貨アカウントとの関係性を
   * TwitterAPIから取得するメソッド
   */
  static public function fetchFriendship($twitter_id, $target_id, $connect)
  {
    // カテゴリーとエンドポイントを指定して、ユーザーのTwitterAPIレートリミットをチェック(上限は 180回/15min)
    $CATEGORY = "friendships";
    $ENDPOINT = "/friendships/show";
    // レートリミットチェック用のメソッドを実行
    $limit = UsersTwitterOAuth::checkLimit($connect, $CATEGORY, $ENDPOINT);

    // フォロー状況チェック（/friendships/show）のレートリミットが上限に達している場合は処理を終了
    if (!$limit) {
      logger()->info("フォロー状況チェックのリクエスト上限に達しました");
      return false;
    }

    // ユーザーのTwitterIDとターゲットのTwitterIDを、TwitterAPIのパラメータに指定
    $params = array(
      'source_id' => $twitter_id,
      'target_id' => $target_id,
    );

    // エンドポイントとパラメータを指定して、TwitterAPIでフォロー状況を取得
    $result = $connect->get($ENDPOINT, $params);

    // 結果を呼び出し元のメソッドに返却
    return $result;
  }

  /**
   * ターゲットの仮想通貨アカウント1件をTwitterAPIでフォローするメソッド
   */
  static public function followTarget($twitter_user, $target_id, $connect)
  {
    // ターゲットのTwitterIDをTwitterAPIのパラメータに指定
    $params = array(
      'user_id' => $target_id,
      'follow' => true, //フォローを相手に通知するか
    );
    // アカウントフォローのエンドポイントを指定
    $ENDPOINT = "friendships/create";
    // フォロー上限は規定されていないため、レートリミットチェックは行わない

    // エンドポイントとパラメータを指定して、TwitterAPIでターゲットをフォロー
    $result = $connect->post($ENDPOINT, $params);

    // 結果が取得できなかった場合はNotFoundエラーを返却
    if (!$result) {
      return abort(404);
    }
    // target_usersテーブルから該当ターゲットのTwitterIDを指定してレコードを取得
    $target = TargetUser::where('twitter_id', $target_id)->first();
    // followsテーブルに登録済みであれば一旦削除
    $twitter_user->follows()->detach($target->id);
    // followsテーブルにユーザーと該当ターゲットのidを登録し、フォロー済みとする
    $twitter_user->follows()->attach($target->id);
    // 結果を呼び出し元のメソッドに返却
    return $result;
  }
}
