<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterFollows extends Model
{
    /**
     * リレーションシップ - target_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function targetUser()
    {
        return $this->belongsTo(
            'App\TargetUser',
            'target_id',
            'twitter_id',
            'target_users'
        );
    }
    /**
     * リレーションシップ - twitter_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function twitterUser()
    {
        return $this->belongsTo(
            'App\TwitterUser',
            'twitter_id',
            'twitter_id',
            'twitter_users'
        );
    }
}
