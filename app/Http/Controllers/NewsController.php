<?php

namespace App\Http\Controllers;

use App\NewsList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * 関連ニュース一覧画面に表示するニュース一覧データ、及び、
 * ホーム画面に表示する最新ニュースデータを
 * DBから取得してJSON形式で返却するコントローラー
 */
class NewsController extends Controller
{
    /**
     * 関連ニュース一覧画面に表示するニュースデータをDBから取得して返却するメソッド
     * 初回表示時などキーワード指定がない場合はDBから全データをページネーション形式で返却し、
     * キーワード検索フォームからキーワードが送信された場合は、該当キーワードを含む
     * ニュースデータのみをページネーション形式で返却する。
     */
    public function index(Request $request)
    {
        // キーワード検索フォームからの検索リクエストがある場合は、検索キーワードを格納
        if ($request->search) {
            $search_word = $request->search;
        } else {
            $search_word = '';
        }

        // 検索キーワードがある場合
        if (!empty($search_word)) {
            // 検索キーワードをタイトルに含むレコードを部分一致で検索し、ニュース公開日の近い順にページネーション形式でJSONデータを返却
            $news = NewsList::where('title', 'LIKE', '%' . $search_word . '%')
                ->orderBy('published_date', 'DESC')
                ->paginate(10);
            return $news;
        }

        // 検索キーワードがない場合は、ニュース一覧をニュース公開日の近い順にページネーション形式でnews_listsテーブルから取得
        $news = NewsList::orderBy('published_date', 'DESC')
            ->paginate(10);

        // 取得できなかった場合はNotFoundエラーを返却
        if (!$news) {
            return abort(404);
        }
        // JSON形式でニュース一覧データを返却
        return $news;
    }

    /**
     * ホーム画面に表示する最新ニュースデータを返却するメソッド
     */
    public function showLatest()
    {
        // ニュース一覧の最新レコードを6件取得
        $news = NewsList::orderBy('published_date', 'DESC')
            ->take(6)->get();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$news) {
            return abort(404);
        }

        // JSON形式で最新ニュースデータを返却
        return $news;
    }
}
