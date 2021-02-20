<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trend extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'currency_name', 'currency_ja', 'currency_pair', 'used_api'
    ];

    // モデルから取得するデータに含めないカラム
    protected $hidden = [
        'deleted_at'
    ];
}
