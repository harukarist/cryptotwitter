<?php

namespace App\Http\Controllers;

use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * トレンド一覧画面に表示する銘柄ランキング一覧データ、及び、
 * ホーム画面に表示する銘柄ランキング上位データを
 * DBから取得してJSON形式で返却するコントローラー
 */
class TrendController extends Controller
{
    /**
     * トレンド一覧画面に表示する銘柄ランキングデータをDBから取得して返却するメソッド
     */
    public function index()
    {
        // trendsテーブルから過去1時間のツイート数の多い順に銘柄ランキングデータを取得
        $trends = Trend::orderBy('tweet_hour', 'desc')->get();

        // trendsテーブルの全レコードのうち、最新の更新日時を取得
        $trend_updated =  Trend::select('updated_at')
            ->orderBy('updated_at', 'desc')->first();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$trends || !$trend_updated) {
            return abort(404);
        }

        // 更新日時データの日時形式を変換
        $updated_at = $trend_updated->updated_at->format('Y.m.d H:i');
        // 銘柄ランキングデータと更新日時を配列に格納
        $items['trends'] = $trends;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す（過去24時間、過去1週間のソートはvue側で行う）
        return json_encode($items);
    }

    /**
     * ホーム画面に表示する銘柄ランキング上位3件をDBから取得して返却するメソッド
     */
    public function showLatest()
    {
        // trendsテーブルから過去1時間・過去24時間・過去1週間それぞれ、
        // ツイート数の多い順に最新レコードを3件ずつ取得
        $trend_hour = Trend::orderBy('tweet_hour', 'DESC')->take(3)->get();
        $trend_day = Trend::orderBy('tweet_day', 'DESC')->take(3)->get();
        $trend_week = Trend::orderBy('tweet_week', 'DESC')->take(3)->get();

        // trendsテーブルの全レコードから最新の更新日時を取得
        $trend_updated =  Trend::select('updated_at')
            ->orderBy('updated_at', 'desc')->first();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$trend_hour || !$trend_updated) {
            return abort(404);
        }

        // 更新日時データの日時形式を変換
        $updated_at = $trend_updated->updated_at->format('Y.m.d H:i');
        
        // 上位3件の通貨情報と更新日時を配列に格納
        $items['trends']['trend_hour'] = $trend_hour;
        $items['trends']['trend_day'] = $trend_day;
        $items['trends']['trend_week'] = $trend_week;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }
}
