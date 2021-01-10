<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterUser extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'user_id',
        'twitter_id',
        'twitter_token',
        'twitter_token_secret',
        'user_name',
        'screen_name',
        'twitter_avatar',
    ];

    /**
     * 返却するJSONに含めない項目
     * @var array
     */
    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];

    /**
     * リレーションシップ - usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * リレーションシップ - twitter_followsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function followList()
    {
        return $this->hasMany('App\TwitterFollows', 'twitter_id', 'twitter_id', 'twitter_follows');
    }
}
