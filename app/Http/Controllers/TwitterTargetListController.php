<?php

namespace App\Http\Controllers;

use App\TargetUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Twitterフォロー画面に表示する「仮想通貨関連アカウント」の一覧データ、及び
 * ホーム画面に表示する最新Twitterアカウント情報をDBから取得して
 * JSON形式で返却するコントローラー
 */
class TwitterTargetListController extends Controller
{
  /**
   * Twitterフォロー画面に表示する「仮想通貨関連アカウント」の一覧データを
   * DBから取得して返却するメソッド。
   * 初回表示時などキーワード指定がない場合はDBから全データをページネーション形式で返却し、
   * キーワード検索フォームからキーワードが送信された場合は、該当キーワードを含む
   * 仮想通貨アカウントのデータのみをページネーション形式で返却する。
   */
  public function index(Request $request)
  {
    // キーワード検索フォームからの検索リクエストがある場合は、検索キーワードを格納
    if ($request->search) {
      $search_word = $request->search;
    } else {
      $search_word = '';
    }

    // 検索キーワードがある場合
    if (!empty($search_word)) {
      // ログインユーザーのTwitterアカウントが未登録の場合
      if (!isset(Auth::user()->twitter_user)) {
        // ツイートIDが存在する有効なアカウント(※)で、かつ
        // 以下のいずれかのカラムに検索キーワードを含む
        // 仮想通貨アカウントデータをDBから取得（クロージャーで複数条件を指定）
        // ※ツイートIDがnull（凍結アカウント、削除済みアカウント、鍵付きアカウント、ツイートが1件もないアカウント）ではないアカウントのみ表示する
        $targets = TargetUser::whereNotNull('tweet_id')
          ->where(function ($query) use ($search_word) {
            $query->where('profile_text', 'LIKE', '%' . $search_word . '%')
              ->orWhere('user_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('screen_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('tweet_text', 'LIKE', '%' . $search_word . '%');
          })
          ->orderBy('id', 'DESC')
          ->paginate(10);
      } else {
        // ログインユーザーのTwitterアカウントが登録済みの場合は未フォローのアカウントのみ取得する
        // フォロー済みの仮想通貨アカウントIDを配列に格納
        $follow_ids = Auth::user()->twitter_user->follows->pluck('id')->toArray();
        // フォロー済み配列のIDを除いた未フォローの有効アカウントで、かつ検索キーワードを含む
        // 仮想通貨アカウントデータをDBから取得（クロージャーで複数条件を指定）
        $targets = TargetUser::whereNotNull('tweet_id')
          ->whereNotIn('id', $follow_ids)
          ->where(function ($query) use ($search_word) {
            $query->where('profile_text', 'LIKE', '%' . $search_word . '%')
              ->orWhere('user_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('screen_name', 'LIKE', '%' . $search_word . '%')
              ->orWhere('tweet_text', 'LIKE', '%' . $search_word . '%');
          })
          ->orderBy('id', 'DESC')
          ->paginate(10);
      }
      // 検索キーワードを含む仮想通貨アカウント一覧を返却
      return $targets;
    }

    // 検索キーワードの指定がなく、ログインユーザーのTwitterアカウントが未登録の場合
    if (!isset(Auth::user()->twitter_user)) {
      // ツイートIDが存在する有効な仮想通貨アカウントをTwitterAPIからの最新取得順にページネーション表示
      $targets = TargetUser::whereNotNull('tweet_id')
        ->orderBy('id', 'DESC')
        ->paginate(10);
      // 自動でJSONに変換して返却
      return $targets;
    }

    // 検索キーワードの指定がなく、ログインユーザーのTwitterアカウントが登録済みの場合
    // フォロー済みの仮想通貨アカウントIDを配列に格納
    $follow_ids = Auth::user()->twitter_user->follows->pluck('id')->toArray();
    // フォロー済み配列のIDを除いた仮想通貨アカウントをTwitterAPIからの最新取得順にページネーション表示
    $targets = TargetUser::whereNotNull('tweet_id')
      ->whereNotIn('id', $follow_ids)
      ->orderBy('id', 'DESC')
      ->paginate(10);

    // 取得できなかった場合は NotFoundエラーを返却
    if (!$targets) {
      return abort(404);
    }
    // JSON形式で返却
    return $targets;
  }

  /**
   * ホーム画面に表示する最新Twitterアカウントデータを返却するメソッド
   */
  public function showLatest()
  {
    // target_usersテーブルから仮想通貨関連アカウントの一覧を
    // 最新取得順→フォロワー数の降順で最新レコードを3件取得
    $targets = TargetUser::whereNotNull('tweet_id')
      ->orderBy('id', 'DESC')
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
