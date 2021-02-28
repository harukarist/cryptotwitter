<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * autofollowsテーブルで、自動フォローを行ったユーザーとターゲットのID
 * (twitter_usersテーブルのidカラム・target_usersテーブルのidカラムの値）
 * を管理するモデル
 */
class Autofollow extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

    /**
     * target_usersテーブルとのリレーションシップ
     */
    public function target_user()
    {
        return $this->belongsTo('App\TargetUser', 'target_id');
    }
    /**
     * twitter_usersテーブルとのリレーションシップ
     */
    public function twitter_user()
    {
        return $this->belongsTo('App\TwitterUser', 'twitter_user_id');
    }
}
