<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrendController extends Controller
{
    /**
     * トレンド一覧表示処理
     */
    public function index()
    {
        // trendsテーブルから全データを取得し、ツイート数の多い順にレコードを並べ替える
        $trends = Trend::orderBy('tweet_hour', 'desc')->get();

        // trendsテーブルの全レコードから最新の更新日時を取得
        $trend_updated =  Trend::select('updated_at')
            ->orderBy('updated_at', 'desc')->first();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$trends || !$trend_updated) {
            return abort(404);
        }

        // 日時形式を変換（Batchモデルの$datesプロパティに'batch_finished_at'カラムを指定する）
        $updated_at = $trend_updated->updated_at->format('Y.m.d H:i');
        // 各通貨の情報と更新日時を配列に格納
        $items['trends'] = $trends;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }

    /**
     * ホーム画面の最新データ表示処理
     */
    public function showLatest()
    {
        // トレンドテーブルからツイート数の多い順に最新レコードを3件取得
        $trend_hour = Trend::orderBy('tweet_hour', 'DESC')->take(3)->get();
        $trend_day = Trend::orderBy('tweet_day', 'DESC')->take(3)->get();
        $trend_week = Trend::orderBy('tweet_week', 'DESC')->take(3)->get();

        // バッチの最終実行日時をバッチテーブルから取得
        $batch =  Batch::select('batch_finished_at')->where('batch_name', 'update_prices')->first();

        // 取得できなかった場合は NotFoundエラーを返却
        if (!$trend_hour || !$batch) {
            return abort(404);
        }

        // 日時形式を変換（Batchモデルの$datesプロパティに'batch_finished_at'カラムを指定する）
        $updated_at = $batch->batch_finished_at->format('Y.m.d H:i');
        // 各通貨の情報と更新日時を配列に格納
        $items['trend_hour'] = $trend_hour;
        $items['trend_day'] = $trend_day;
        $items['trend_week'] = $trend_week;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }
}
