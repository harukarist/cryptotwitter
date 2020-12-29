<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    //$keyword:ニュース検索のキーワード
    //$max_num:取得記事数の上限
    public function getNews()
    {
        // PHPの最大実行時間
        set_time_limit(90);

        $keywords = '仮想通貨';
        $max_num = 50;

        // Google News RSSフィード
        // キーワード検索のベースURL 
        $API_BASE_URL = "https://news.google.com/rss/search?q=";
        // $API_BASE_URL = "https://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=atom&q=";

        // キーワードの文字コードを変換
        $query = urlencode(mb_convert_encoding($keywords, "UTF-8", "auto"));
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
            $lists[$i]['date'] =  (string)$items_arr[$i]->pubDate;
            $lists[$i]['source'] =  (string)$items_arr[$i]->source;
        }

        // JSON形式に変換してビューに渡す
        $lists_json = json_encode($lists);
        dd($lists_json, $lists);

        return view('news', compact('lists_json'));
    }
}
