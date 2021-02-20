<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsList extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'title',
        'url',
        'published_date',
        'source',
    ];

    /**
     * 返却するJSONに含めない項目
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    // 日付のデータ属性を指定
    protected $dates = [
        'published_date',
    ];

    // 日付のフォーマット
    public function getPublishedDateAttribute($value)
    {
        return Carbon::parse($value)->format("Y.m.d");
    }
}
