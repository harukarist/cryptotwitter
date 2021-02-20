<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follow extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;
}
