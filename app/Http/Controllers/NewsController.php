<?php

namespace App\Http\Controllers;

use App\NewsList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends Controller
{
    // ニュース画面表示処理
    public function index()
    {
        // ニュース一覧をテーブルから取得してニュース公開日の近い順にページネーション 
        $news = NewsList::orderBy('published_date', 'DESC')
            ->paginate(10);

        // 自動でJSONに変換して返却される
        return $news;
    }
}
