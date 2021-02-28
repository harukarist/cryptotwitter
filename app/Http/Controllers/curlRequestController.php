<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * cURLを使用して外部APIから情報を取得する処理を共通化するためのクラス
 */
class curlRequestController extends Controller
{
    /**
     * 引数でアクセス先のURLを受け取り、cURLで該当URLへのHTTPリクエストを行い、
     * 取得結果をJSON形式に変換して返却するメソッド
     */
    public static function curl($url)
    {
        // cURLセッションを初期化
        $ch = curl_init();

        // cURLの転送オプションを設定
        curl_setopt($ch, CURLOPT_URL, $url); // アクセス先URLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // レスポンスを出力せず文字列で受け取る
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //移動先のURLへリクエスト

        // cURLセッションを実行し、アクセス先URLからレスポンスを受け取る
        $response = curl_exec($ch);

        // cURLセッションを終了
        curl_close($ch);

        // レスポンスをJSON形式にデコードして呼び出し元へ返却
        $response_json = json_decode($response);
        return $response_json;
    }
}
