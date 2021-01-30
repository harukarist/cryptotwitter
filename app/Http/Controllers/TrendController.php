<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrendController extends Controller
{
    // トレンド表示処理
    public function index()
    {
        // トレンドテーブルから全データを取得し、ツイート数の多い順にレコードを並べ替える
        $trends = Trend::orderBy('tweet_hour', 'DESC')->get();
        // $trends = Trend::all();

        // バッチの最終実行日時をバッチテーブルから取得
        $batch =  Batch::select('batch_finished_at')->where('batch_name', 'update_prices')->first();
        // 日時形式を変換（Batchモデルの$datesプロパティに'batch_finished_at'カラムを指定する）
        $updated_at = $batch->batch_finished_at->format('Y/m/d H:i');
        // 各通貨の情報と更新日時を配列に格納
        $items['trends'] = $trends;
        $items['updated_at'] = $updated_at;

        // JSON形式に変換してビューに渡す
        return json_encode($items);
    }
}
