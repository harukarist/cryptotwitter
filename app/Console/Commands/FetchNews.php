<?php

namespace App\Console\Commands;

use App\NewsList;
use Illuminate\Console\Command;
use App\Http\Controllers\FetchNewsController;


/**
 * GoogleニュースAPIで仮想通貨関連のキーワードを含むニュースを検索して
 * DBに保存するコマンド
 */
class FetchNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:news';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'Googleニュースを検索してDBに保存';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * コマンドで実行するメソッド
     * @return mixed
     */
    public function handle()
    {
        //ログファイルに書き込む
        logger()->info('>>>> GoogleNews保存バッチを実行します');

        // Googleニュースの検索キーワード
        $KEYWORDS = ['仮想通貨', '暗号資産', 'ビットコイン'];

        // キーワードを指定してGoogleNewsRSSでニュース取得するメソッドを実行
        foreach ($KEYWORDS as $keyword) {
            $this->requestAndCreate($keyword);
        }
        logger()->info('GoogleNews保存バッチを実行しました <<<<');
    }

    /**
     * GoogleNewsRSSで指定キーワードを含むGoogleニュースを取得するメソッド
     */
    public function requestAndCreate($keyword)
    {
        set_time_limit(90); // PHPの最大実行時間を指定
        $max_num = 100; // 保存する記事数（取得上限は100件）

        // キーワード検索のベースURL 
        $API_BASE_URL = "https://news.google.com/rss/search?q=";
        // 日本語検索のオプションURL
        $OPTION = '&hl=ja&gl=JP&ceid=JP:ja';

        // キーワードの文字コードを変換
        $query = urlencode(mb_convert_encoding($keyword, "UTF-8", "auto"));

        // APIへのリクエストURLを生成
        $api_url = $API_BASE_URL  . $query . $OPTION;

        // APIにアクセスしてニュース情報のXML文字列を取得
        $xml = file_get_contents($api_url);

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

            // 同一タイトルのニュースがあればレコードを更新、同一タイトルのニュースがなければ新規作成する
            NewsList::updateOrCreate([
                'title' => $lists[$i]['title']
            ], [
                'title' => $lists[$i]['title'],
                'url' => $lists[$i]['url'],
                'published_date' => $lists[$i]['date'],
                'source' => $lists[$i]['source']
            ]);
        }
        dump("{$keyword}のニュースを{$max_num}件保存しました");
        logger()->info("{$keyword}のニュースを{$max_num}件保存しました");
        return;
    }
}
