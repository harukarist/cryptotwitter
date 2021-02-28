<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仮想通貨銘柄の通貨名、高値・安値の価格、
 * ツイート取得用の検索キーワードやツイート数などを
 * trendsテーブルで管理するためのモデル
 */
class Trend extends Model
{
    // ソフトデリート用のトレイトを追加
    use SoftDeletes;

    // 書き込みを許可するカラムの指定
    protected $fillable = [
        'currency_name', 'currency_ja', 'currency_pair', 'used_api'
    ];

    // モデルから取得するデータに含めないカラムの指定
    protected $hidden = [
        'deleted_at'
    ];
}
