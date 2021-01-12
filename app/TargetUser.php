<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetUser extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

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

    /**
     * 返却するJSONに含める項目
     * @var array
     */
    // ログインユーザーがターゲットをフォロー済みかどうかを表示
    protected $appends = [
        'followed_by_user',
    ];

    /**
     * 返却するJSONに含めない項目
     * @var array
     */
    protected $hidden = [
        'tweet_id', self::CREATED_AT, self::UPDATED_AT,
    ];

    /**
     * アクセサ - followed_by_user
     * @return boolean
     */
    public function getFollowedByUserAttribute()
    {
        // ユーザーがログイン前の場合はfalseを返す
        if (Auth::guest()) {
            return false;
        }
        // ログインユーザーが該当Twitterアカウントをフォロー済みかを返す（followsテーブルにログインユーザーのtwitterIDと該当TwitterアカウントのIDのセットが存在するか）
        return $this->follows->contains(Auth::user()->twitter_user->id);
    }

    /**
     * リレーションシップ - followsテーブル,twitter_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function follows()
    {
        // TwitterUserとTargetUserは、followsテーブルを中間テーブルとした多対多の関係
        // 第３引数はリレーションを定義しているモデルの外部キー名、第４引数は結合するモデルの外部キー名
        // withTimestamps()で、followsテーブルへの操作時にタイムスタンプを更新する設定を追加
        return $this->belongsToMany('App\TwitterUser', 'follows', 'target_id', 'twitter_user_id')
            ->withTimestamps();
    }
}
