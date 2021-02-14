<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'name', 'email', 'message'
    ];
}
