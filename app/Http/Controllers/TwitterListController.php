<?php

namespace App\Http\Controllers;

use App\TargetUser;
use Illuminate\Http\Request;

class TwitterListController extends Controller
{
  /**
   * Twitterアカウント一覧表示処理
   */
  public function index()
  {
    // twitter_followersテーブルを結合してフォロー対象かどうかを表示
    // $targets = TargetUser::with(['twitter_follows'])
    //   ->orderBy('follower_num', 'DESC')
    //   ->orderBy(TargetUser::CREATED_AT, 'DESC')
    //   ->paginate();
    $targets = TargetUser::orderBy('follower_num', 'DESC')
      ->orderBy(TargetUser::CREATED_AT, 'DESC')
      ->paginate(5);

    // モデルクラスのインスタンスは自動でJSONに変換して返却
    return $targets;
  }
}
