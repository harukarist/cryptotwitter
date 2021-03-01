<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GoogleNewsRSSで取得したGoogleニュースのデータを
 * news_listsテーブルで管理するためのモデル
 */
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
     * モデルから取得するデータに含めないカラムの指定
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
