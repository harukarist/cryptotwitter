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

    // /**
    //  * 返却するJSONに含める項目
    //  * @var array
    //  */
    // // twitter_followersテーブルを結合してフォロー対象かどうかを表示
    // protected $appends = [
    //     'target_id',
    // ];

    // /**
    //  * 返却するJSONに含めない項目
    //  * @var array
    //  */
    // protected $hidden = [
    //     'tweet_id', self::CREATED_AT, self::UPDATED_AT,
    // ];

    // /**
    //  * リレーションシップ - twitter_followsテーブル
    //  * @return \Illuminate\Database\Eloquent\Relations\hasMany
    //  */
    // public function twitter_follows()
    // {
    //     return $this->hasMany('App\TwitterFollows', 'twitter_id', 'target_id', 'twitter_follows');
    // }
}
