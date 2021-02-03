<?php

namespace App\Http\Controllers;

use App\TargetUser;
use App\TwitterUser;
use Illuminate\Http\Request;
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
    $targets = TargetUser::orderBy('created_at', 'DESC')
      ->orderBy('follower_num', 'DESC')
      ->paginate(10);
    // 自動でJSONに変換して返却
    return $targets;
  }
}
