<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Autofollow extends Model
{
    /**
     * リレーションシップ - target_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function target_user()
    {
        return $this->belongsTo('App\TargetUser', 'target_id');
    }
    public function twitterUser()
    {
        return $this->belongsTo('App\TwitterUser', 'twitter_user_id');
    }
}
