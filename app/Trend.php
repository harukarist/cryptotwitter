<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'currency_name', 'currency_ja', 'currency_pair', 'used_api'
    ];
}
