<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use App\Facades\Twitter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;

class FollowTargetController extends Controller
{
  /**
   * ターゲット1件をフォロー
   */
  public function createFollow(string $target_id)
  {
    $twitter_user = Auth::user()->twitter_user;
    if (!$twitter_user) {
      abort(404);
    }

    $twitter_id = $twitter_user->twitter_id;

    // ターゲットをフォロー済みかどうかをチェック
    $is_following = $this->checkIsFollowing($twitter_id, $target_id);

    // すでにフォロー済みの場合は何もせずに返却
    if ($is_following) {
      return [
        'message' => 'アカウントはフォロー済みです',
        'target_id' => $target_id
      ];
    }

    // ターゲットをフォロー
    $result = $this->followTarget($twitter_user, $target_id);
    // dd($result);

    // return redirect('/twitter')->with('flash_message', __('アカウントをフォローしました'));
    return [
      'message' => 'アカウントをフォローしました',
      'target_id' => $target_id
    ];
  }

  /**
   * ターゲット1件をフォロー解除
   */
  public function destroyFollow(string $target_id)
  {
    $twitter_user = Auth::user()->twitter_user;
    if (!$twitter_user) {
      abort(404);
    }

    $twitter_id = $twitter_user->twitter_id;

    // ターゲットをフォロー済みかどうかをチェック
    $is_following = $this->checkIsFollowing($twitter_id, $target_id);

    // フォローしていない場合は何もせずに返却
    if (!$is_following) {
      return [
        'message' => 'このアカウントをフォローしていません',
        'target_id' => $target_id
      ];
    }

    // ターゲットをフォロー解除
    $result = $this->unfollowTarget($twitter_user, $target_id);
    // dd($result);

    // return redirect('/twitter')->with('flash_message', __('アカウントをフォローしました'));
    return [
      'message' => 'アカウントをフォロー解除しました',
      'target_id' => $target_id
    ];
  }

  /**
   * フォロー済みかどうかをチェック
   */
  public function checkIsFollowing($twitter_id, $target_id)
  {
    $params = array(
      'source_id' => $twitter_id,
      'target_id' => $target_id,
    );

    $result = \Twitter::get("friendships/show", $params);
    // dd($result);

    if (!$result) {
      return abort(404);
    }
    $is_following = $result->relationship->source->following;
    return $is_following;
  }

  /**
   * ターゲットをフォロー
   */
  public function followTarget($twitter_user, $target_id)
  {

    $params = array(
      'user_id' => $target_id,
      'follow' => true, //フォローを相手に通知するか
    );

    // TwitterAPIでターゲットをフォロー
    $connect = $this->userOAuth($twitter_user);
    $result = $connect->post("friendships/create", $params);

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

  /**
   * ターゲットをフォロー解除
   */
  public function unfollowTarget($twitter_user, $target_id)
  {

    $params = array(
      'user_id' => $target_id,
    );

    // TwitterAPIでターゲットをフォロー解除
    $connect = $this->userOAuth($twitter_user);
    $result = $connect->post("friendships/destroy", $params);

    if (!$result) {
      return abort(404);
    }

    // target_usersテーブルから該当TwitterIDのレコードを取得
    $target = TargetUser::where('twitter_id', $target_id)->first();
    // followsテーブルからユーザーとフォロー相手のidを削除
    $twitter_user->follows()->detach($target->id);

    return $result;
  }

  /**
   * ログインユーザーのトークンでOAuth認証
   */
  public function userOAuth($twitter_user)
  {
    $user_token = $twitter_user->twitter_token;
    $user_secret = $twitter_user->twitter_token_secret;

    // ヘルパー関数config()でconfig/twitter.phpを参照してインスタンスを作成
    $config = config('twitter');
    //接続に必要な接続インスタンスを生成
    $connect = new TwitterOAuth(
      $config['api_key'],
      $config['secret_key'],
      $user_token,
      $user_secret,
    );
    // dd($connect);
    // dd($config['api_key']);
    return $connect;
  }
}
