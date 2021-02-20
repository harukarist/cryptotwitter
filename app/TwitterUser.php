<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TwitterUser extends Model
{
    // ソフトデリート用のSoftDeletesトレイトを使用
    use SoftDeletes;

    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'user_id',
        'twitter_id',
        'twitter_token',
        'twitter_token_secret',
        'user_name',
        'screen_name',
        'twitter_avatar',
        'use_autofollow',
    ];

    /**
     * 返却するJSONに含めない項目
     * @var array
     */
    protected $hidden = [
        'twitter_token', 'twitter_token_secret', 'created_at', 'updated_at'
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
     * リレーションシップ - followsテーブル,autofollowsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function follows()
    {
        // TwitterUserとTargetUserは、followsテーブルを中間テーブルとした多対多の関係
        // 第３引数は中間テーブルにおけるこのモデルの外部キー名、第４引数は結合するモデルの外部キー名
        // withTimestamps()で、followsテーブルへの操作時にタイムスタンプを更新する設定を追加
        return $this->belongsToMany('App\TargetUser', 'follows', 'twitter_user_id', 'target_id')
            ->withTimestamps();
    }
    public function autofollows()
    {
        // TwitterUserとTargetUserは、autofollowsテーブルを中間テーブルとした多対多の関係
        return $this->belongsToMany('App\TargetUser', 'autofollows', 'twitter_user_id', 'target_id')
            ->withTimestamps();
    }
}
