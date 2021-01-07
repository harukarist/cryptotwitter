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

        foreach ($trends as $trend) {
            // 通貨ペアがある通貨は最高価格、最安価格の数値をカンマ区切りにフォーマット
            if ($trend->currency_pair) {
                $trend->high = number_format($trend->high) . ' 円';
                $trend->low = number_format($trend->low) . ' 円';
            } else {
                // 通貨ペアが登録されていない通貨は'不明'を表示
                $trend->high = '不明';
                $trend->low = '不明';
            }
            $trend->tweet_hour = number_format($trend->tweet_hour);
            $trend->tweet_day = number_format($trend->tweet_day);
            $trend->tweet_week = number_format($trend->tweet_week);
        }
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
