<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * autofollows_logsテーブルで、
 * 自動フォローを行ったユーザーのID(twitter_usersテーブルのidカラムの値）と
 * 自動フォロー合計数、残りフォロー可能回数を管理するモデル
 */
class AutofollowLog extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;
}
