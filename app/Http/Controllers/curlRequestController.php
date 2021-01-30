<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class curlRequestController extends Controller
{
    public static function curl($url)
    {
        // cURLセッションを初期化
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url); // アクセス先URLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // レスポンスを出力せず文字列で受け取る
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //移動先のURLへリクエスト

        // アクセス先URLからレスポンスを受け取る
        $response = curl_exec($ch);

        // cURLの終了
        curl_close($ch);

        // レスポンスをJSON形式にデコードして返却
        $response_json = json_decode($response);
        return $response_json;
    }

    public static function file($url)
    {
        // file_get_contents()で取得
        $http_response_header = null;
        $response = file_get_contents($url);
        $header = $http_response_header;

        // レスポンスをJSON形式にデコード
        $response_json = json_decode($response);
        return $response_json;
    }
}
