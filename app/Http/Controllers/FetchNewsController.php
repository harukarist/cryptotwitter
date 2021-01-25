<?php

namespace App\Http\Controllers;

use App\NewsList;
use App\Http\Controllers\Controller;

class FetchNewsController extends Controller
{
    public function fetchNews()
    {
        $keyword = '仮想通貨';
        $this->requestAndCreate($keyword);

        $keyword = '暗号資産';
        $this->requestAndCreate($keyword);
    }

    public function requestAndCreate($keyword)
    {

        set_time_limit(90); // PHPの最大実行時間
        $max_num = 100; // 保存する記事数（取得上限は100件）

        // Google News RSSフィード
        // キーワード検索のベースURL 
        $API_BASE_URL = "https://news.google.com/rss/search?q=";
        // $API_BASE_URL = "https://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=atom&q=";

        // キーワードの文字コードを変換
        $query = urlencode(mb_convert_encoding($keyword, "UTF-8", "auto"));
        // 日本語検索のオプションURL
        $lang = '&hl=ja&gl=JP&ceid=JP:ja';

        // APIへのリクエストURLを生成
        $url = $API_BASE_URL  . $query . $lang;

        // APIにアクセスしてニュース情報のXML文字列を取得
        $xml = file_get_contents($url);
        // XML文字列を読み込んでSimpleXMLElementクラスのオブジェクトを返す
        $obj = simplexml_load_string($xml);

        // オブジェクトの記事データ配列を取得して変数に格納
        $items_arr = $obj->channel->item;
        // 記事タイトル、記事タイトルなどを入れる配列を用意
        $lists = [];

        // 取得したニュース数が上限値より少ない場合は、取得したニュース数で上限値を上書き
        if (count($items_arr) < $max_num) {
            $max_num = count($items_arr);
        }

        // 上限値まで記事データ配列をループ
        for ($i = 0; $i < $max_num; $i++) {
            // 記事タイトルの文字エンコーディングを変換して配列に格納
            $lists[$i]['title'] = mb_convert_encoding($items_arr[$i]->title, "UTF-8", "auto");

            // 記事のURL（SimpleXMLElementオブジェクト）をstringにキャストして配列に格納
            $lists[$i]['url'] =  (string)$items_arr[$i]->link;
            // 配信日時をstrtotime()でUNIXタイムスタンプに変換し、date()で表示形式を指定
            // $lists[$i]['date'] =  date('Y.m.d(D)', strtotime($items_arr[$i]->pubDate));
            $lists[$i]['date'] =  date('Y-m-d', strtotime($items_arr[$i]->pubDate));
            // 記事の配信元をstringにキャストして配列に格納
            $lists[$i]['source'] =  (string)$items_arr[$i]->source;

            NewsList::updateOrCreate([
                'title' => $lists[$i]['title']
            ], [
                'title' => $lists[$i]['title'],
                'url' => $lists[$i]['url'],
                'published_date' => $lists[$i]['date'],
                'source' => $lists[$i]['source']
            ]);
        }
        logger()->info("{$keyword}のニュースを{$max_num}件保存しました");
        return;
    }
}
