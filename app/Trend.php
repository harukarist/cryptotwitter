<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    protected $fillable = [
        'currency_name', 'currency_ja', 'currency_pair', 'used_api'
    ];
}
