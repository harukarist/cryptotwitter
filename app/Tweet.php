<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * TwitterAPIで取得した過去1週間以内のツイートデータを
 * tweetsテーブルで管理するためのモデル
 */
class Tweet extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'tweet_id', 'tweet_text', 'tweeted_at', 'twitter_user_id', 'twitter_user_name', 'screen_name', 'description', 'next_results'
    ];

    // 日付のデータ属性を指定
    protected $dates = [
        'tweeted_at'
    ];
}
