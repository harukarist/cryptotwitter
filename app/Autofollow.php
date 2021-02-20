<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autofollow extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

    /**
     * リレーションシップ - target_usersテーブル,twitter_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function target_user()
    {
        return $this->belongsTo('App\TargetUser', 'target_id');
    }
    public function twitter_user()
    {
        return $this->belongsTo('App\TwitterUser', 'twitter_user_id');
    }
}
