<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TwitterTargetListController extends Controller
{
  /**
   * Twitterアカウント一覧取得処理
   */
  public function index()
  {
    // target_usersテーブルから仮想通貨関連アカウントの一覧を取得して
    // 最新取得日→フォロワー数の降順でページネーション表示

    // ログインユーザーのTwitterアカウントが未登録の場合
    if (!isset(Auth::user()->twitter_user)) {
      // 仮想通貨アカウントをAPIからの最新取得順にページネーション表示
      $targets = TargetUser::orderBy('created_at', 'DESC')
        ->paginate(10);
    } else {
      // ログインユーザーのTwitterアカウントが登録済みの場合
      // フォロー済みの仮想通貨アカウントIDを配列に格納
      $follow_ids = Auth::user()->twitter_user->follows->pluck('id')->toArray();
      // フォロー済みを除いた仮想通貨アカウントをAPIからの最新取得順にページネーション表示
      $targets = TargetUser::whereNotIn('id', $follow_ids)
        ->orderBy('created_at', 'DESC')->paginate(10);
    }

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
    $targets = TargetUser::orderBy('created_at', 'DESC')
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
