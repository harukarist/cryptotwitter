<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutofollowLog extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

}
