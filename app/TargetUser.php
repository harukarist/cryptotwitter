<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TwitterAPIで取得した仮想通貨関連アカウントを
 * target_usersテーブルで管理するためのモデル
 */
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
    // ログインユーザーがフォロー済みの仮想通貨アカウントかどうかをアクセサで表示
    protected $appends = [
        'followed_by_user',
    ];

    /**
     * モデルから取得するデータに含めないカラムの指定
     * @var array
     */
    protected $hidden = [
        'updated_at', 'deleted_at'
    ];


    // 日付のフォーマット
    public function getTweetedAtAttribute($value)
    {
        return Carbon::parse($value)->format("Y.m.d H:m");
    }

    /**
     * それぞれの仮想通貨アカウントについて、
     * ログインユーザーのTwitterアカウントがフォロー済みかどうかを
     * true/falseで返却するアクセサ（followed_by_user）
     * @return boolean
     */
    public function getFollowedByUserAttribute()
    {
        // ユーザーがTwitter連携前の場合はfalseを返す
        if (!isset(Auth::user()->twitter_user)) {
            return false;
        }
        // target_usersテーブルに保存された仮想通貨アカウントの各々について、
        // followsテーブルに、ログインユーザーに紐づくtwitter_usersテーブルのidと
        // target_usersテーブルのidのセットが存在するかどうかを。true/falseで返却
        return $this->follows->contains(Auth::user()->twitter_user->id);
    }

    /**
     * followsテーブルとのリレーションシップ（多対多）
     */
    public function follows()
    {
        // TwitterUserとTargetUserは、followsテーブルを中間テーブルとした多対多の関係
        return $this->belongsToMany('App\TwitterUser', 'follows', 'target_id', 'twitter_user_id')
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
        return $this->belongsToMany('App\TwitterUser', 'autofollows', 'target_id', 'twitter_user_id')
            ->withTimestamps();
    }
}
