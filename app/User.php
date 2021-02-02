<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    // ソフトデリート用のSoftDeletesトレイトを使用
    use SoftDeletes;

    // 通知用のNotifiableトレイトを使用
    use Notifiable;

    /**
     * 値の代入を許可するカラム
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'twitter_id', 'avatar'
    ];

    /**
     * サーバーから返却するレスポンスに含めないカラム
     * The attributes that should be hidden for arrays.
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', self::CREATED_AT, self::UPDATED_AT,
    ];

    /**
     * DBから取得した値の型を変換するカラム
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * リレーションシップ - twitter_usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function twitter_user()
    {
        return $this->hasOne('App\TwitterUser');
    }
}
