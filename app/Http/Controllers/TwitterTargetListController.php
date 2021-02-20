<?php

namespace App\Http\Controllers;

use App\TargetUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * ログイン後に最初に表示されるホーム画面と、
 * Twitterフォロー画面に表示する仮想通貨関連アカウントの情報を
 * JSON形式で返却するクラス
 */
class TwitterTargetListController extends Controller
{
  /**
   * Twitterアカウント一覧取得処理
   */
  public function index(Request $request)
  {
    // 検索リクエストがあった場合は検索キーワードを格納
    if ($request->search) {
      $search_word = $request->search;
    } else {
      $search_word = '';
    }

    // 検索キーワードがある場合
    if (!empty($search_word)) {
      // ログインユーザーのTwitterアカウントが未登録の場合
      if (!isset(Auth::user()->twitter_user)) {
        // ツイートIDが存在する有効なアカウントで、かつ以下のいずれかのカラムに検索キーワードを含む
        // 仮想通貨アカウントを取得（クロージャーで複数条件を指定）
        // ツイートIDがnullのレコードは除く（凍結アカウント、削除済みアカウント、鍵付きアカウント、ツイートが1件もないアカウントのため、仮想通貨アカウント一覧には表示しない）
        $targets = TargetUser::whereNotNull('tweet_id')
          ->where(function ($query) use ($search_word) {
            $query->where('profile_text', 'LIKE', '%' . $search_word . '%')
              ->orWhere('user_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('screen_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('tweet_text', 'LIKE', '%' . $search_word . '%');
          })
          ->orderBy('created_at', 'DESC')
          ->paginate(10);
      } else {
        // ログインユーザーのTwitterアカウントが登録済みの場合
        // フォロー済みの仮想通貨アカウントIDを配列に格納
        $follow_ids = Auth::user()->twitter_user->follows->pluck('id')->toArray();
        // フォロー済みを除いた仮想通貨アカウントをAPIからの最新取得順にページネーション表示
        $targets = TargetUser::whereNotNull('tweet_id')
          ->whereNotIn('id', $follow_ids)
          ->where(function ($query) use ($search_word) {
            $query->where('profile_text', 'LIKE', '%' . $search_word . '%')
              ->orWhere('user_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('screen_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('tweet_text', 'LIKE', '%' . $search_word . '%');
          })
          ->orderBy('created_at', 'DESC')
          ->paginate(10);
      }
      // 検索キーワードを含む仮想通貨アカウント一覧を返却
      return $targets;
    }

    // 検索キーワードの指定がなく、ログインユーザーのTwitterアカウントが未登録の場合
    if (!isset(Auth::user()->twitter_user)) {
      // ツイートIDが存在する有効な仮想通貨アカウントをAPIからの最新取得順にページネーション表示
      $targets = TargetUser::whereNotNull('tweet_id')
        ->orderBy('created_at', 'DESC')->paginate(10);
      // 自動でJSONに変換して返却
      return $targets;
    }


    // 検索キーワードの指定がなく、ログインユーザーのTwitterアカウントが登録済みの場合
    // フォロー済みの仮想通貨アカウントIDを配列に格納
    $follow_ids = Auth::user()->twitter_user->follows->pluck('id')->toArray();
    // フォロー済みを除いた仮想通貨アカウントをAPIからの最新取得順にページネーション表示
    $targets = TargetUser::whereNotNull('tweet_id')
      ->whereNotIn('id', $follow_ids)
      ->orderBy('created_at', 'DESC')->paginate(10);

    clock($targets);
    // 取得できなかった場合は NotFoundエラーを返却
    if (!$targets) {
      return abort(404);
    }
    // 自動でJSONに変換して返却
    return $targets;
  }

  /**
   * ホーム画面の最新データ表示処理
   */
  public function showLatest()
  {
    // target_usersテーブルから仮想通貨関連アカウントの一覧を
    // 最新取得日→フォロワー数の降順で最新レコードを3件取得
    $targets = TargetUser::whereNotNull('tweet_id')
      ->orderBy('created_at', 'DESC')
      ->orderBy('follower_num', 'DESC')
      ->take(3)->get();

    // 取得できなかった場合は NotFoundエラーを返却
    if (!$targets) {
      return abort(404);
    }
    // 自動でJSONに変換して返却
    return $targets;
  }
}
