<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'batch_name', 'batch_finished_at'
    ];

    // 日付のデータ属性を指定
    protected $dates = [
        'batch_finished_at'
    ];
}
