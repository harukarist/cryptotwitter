<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Twitterアカウントを登録しているユーザーの
 * フォロー済み仮想通貨アカウントを
 * followsテーブルで管理するためのモデル
 */
class Follow extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;
}
