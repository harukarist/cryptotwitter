<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetUser extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'twitter_id',
        'user_name',
        'screen_name',
        'follow_num',
        'follower_num',
        'profile_text',
        'profile_img',
        'tweet_id',
        'tweet_text',
        'tweeted_at',
        'url',
    ];

    // 日付のデータ属性を指定
    protected $dates = [
        'tweeted_at',
    ];
}
