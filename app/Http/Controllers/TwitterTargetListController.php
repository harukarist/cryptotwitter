<?php

namespace App\Http\Controllers;

use App\TargetUser;
use Illuminate\Http\Request;

class TwitterTargetListController extends Controller
{
  /**
   * Twitterアカウント一覧表示処理
   */
  public function index()
  {
    // followsテーブルへのリレーションを結合してフォロー済みかどうかを表示
    $targets = TargetUser::with(['follows'])
      ->orderBy('follower_num', 'DESC')
      ->orderBy(TargetUser::CREATED_AT, 'DESC')
      ->paginate(10);

    // 自動でJSONに変換して返却される
    return $targets;
  }
}
