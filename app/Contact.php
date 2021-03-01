<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * お問い合わせフォームに入力された内容を
 * contactsテーブルに保存するためのモデル
 */
class Contact extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'name', 'email', 'message'
    ];
}
