<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ログインユーザーがTwitterOAuth認証したTwitterアカウント情報を
 * twitter_usersテーブルで管理するためのモデル
 */
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
     * モデルから取得するデータに含めないカラムの指定
     * @var array
     */
    protected $hidden = [
        'twitter_token', 'twitter_token_secret', 'created_at', 'updated_at'
    ];

    /**
     * usersテーブルとのリレーションシップ（1対1）
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * followsテーブルとのリレーションシップ（多対多）
     */
    public function follows()
    {
        // TwitterUserとTargetUserは、followsテーブルを中間テーブルとした多対多の関係
        return $this->belongsToMany('App\TargetUser', 'follows', 'twitter_user_id', 'target_id')
            ->withTimestamps();
        // 第３引数は中間テーブルにおけるこのモデルの外部キー名、第４引数は結合するモデルの外部キー名
        // withTimestamps()で、followsテーブルへの操作時にタイムスタンプを更新する設定を追加
    }

    /**
     * autofollowsテーブルとのリレーションシップ（多対多）
     */
    public function autofollows()
    {
        // TwitterUserとTargetUserは、autofollowsテーブルを中間テーブルとした多対多の関係
        return $this->belongsToMany('App\TargetUser', 'autofollows', 'twitter_user_id', 'target_id')
            ->withTimestamps();
    }
}
