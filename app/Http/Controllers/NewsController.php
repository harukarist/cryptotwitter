<?php

namespace App\Http\Controllers;

use App\NewsList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends Controller
{
    /**
     * ニュース画面表示処理
     */
    public function index(Request $request)
    {
        // 検索リクエストがあった場合は検索キーワードを格納
        if ($request->search) {
            $search_word = $request->search;
        } else {
            $search_word = '';
        }

        // 検索キーワードがある場合
        if (isset($search_word)) {
            $news = NewsList::where('title', 'LIKE', '%' . $search_word . '%')
                ->orderBy('published_date', 'DESC')
                ->paginate(10);
            return $news;
        }
        // ニュース一覧をテーブルから取得してニュース公開日の近い順にページネーション 
        $news = NewsList::orderBy('published_date', 'DESC')
            ->paginate(10);

        // 取得できなかった場合はNotFoundエラーを返却
        if (!$news) {
            return abort(404);
        }

        // 自動でJSONに変換して返却される
        return $news;
    }

    /**
     * ホーム画面の最新データ表示処理
     */
    public function showLatest()
    {
        // ニュース一覧の最新レコードを5件取得
        $news = NewsList::orderBy('published_date', 'DESC')
            ->take(6)->get();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$news) {
            return abort(404);
        }

        // 自動でJSONに変換して返却される
        return $news;
    }
}
