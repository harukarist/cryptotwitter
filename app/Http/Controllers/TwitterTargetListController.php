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
   * Twitterアカウント一覧表示処理
   */
  public function index()
  {
    // $user_id = Auth::id();
    // // twitter_usersテーブルからログインユーザーのTwitterアカウント情報を取得
    // $twitter_user = TwitterUser::select('id', 'user_name', 'screen_name', 'twitter_avatar', 'use_autofollow')
    //   ->where('user_id', $user_id)->first();

    // if ($twitter_user) {
    //   // followsテーブルへのリレーションを結合してフォロー済みかどうかを表示
    //   $targets = TargetUser::with(['follows'])
    //     ->orderBy('follower_num', 'DESC')
    //     ->orderBy(TargetUser::CREATED_AT, 'DESC')
    //     ->paginate(10);
    // } else {
    //   $targets = TargetUser::orderBy('follower_num', 'DESC')
    //     ->orderBy(TargetUser::CREATED_AT, 'DESC')
    //     ->paginate(10);
    // }

    $targets = TargetUser::orderBy('created_at', 'DESC')
      ->orderBy('follower_num', 'DESC')
      ->paginate(10);
    // 自動でJSONに変換して返却される
    return $targets;
  }
}
