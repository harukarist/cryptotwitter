<?php

namespace App\Http\Controllers;

use App\Trend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TweetController extends Controller
{
    // キーワードを含むツイートを取得して保存する処理
    public function getTweet()
    {
        $bearer_token = 'AAAAAAAAAAAAAAAAAAAAAGE1LAEAAAAAHsZcrDgnnO3Je0XRBXNlZMbcRKQ%3DHtfSfMmaE6d4Z21HqJYv6G6yzfsqLtIIunLf3h4S5BnHpgF1MN';
        $request_url = 'https://api.twitter.com/1.1/search/tweets.json';
        // $request_url = 'https://api.twitter.com/2/tweets/search/recent';

        $keywords = 'ETH BTC';

        // パラメータ (オプション)
        $options = array(
            'q' => $keywords,
            'count' => '100',
            'lang' => 'ja',
            'locale' => 'ja',
            'result_type' => 'recent',
            // "until" => "2017-01-17",
            // "max_id" => "643299864344788992",
            // "include_entities" => "true",
        );

        // $since_id = getMaxID(); //DBから現在の最大TweetIDを取得する処理
        // if ($since_id) {
        //     $options['since_id'] = $since_id; //前回の最後に取得したツイートIDから
        // }

        // パラメータがある場合
        if ($options) {
            $request_url .= '?' . http_build_query($options);
        }


        // リクエスト用のコンテキスト
        $context = array(
            'http' => array(
                'method' => 'GET', // リクエストメソッド
                'header' => array(              // ヘッダー
                    'Authorization: Bearer ' . $bearer_token,
                ),
            ),
        );

        // cURLを使ってリクエスト
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']); // メソッド
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // curl_execの結果を文字列で返す
        curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http']['header']); // ヘッダー
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); // タイムアウトの秒数
        $res1 = curl_exec($curl);
        $res2 = curl_getinfo($curl);
        curl_close($curl);

        // 取得したデータ
        $json_data = substr($res1, $res2['header_size']); // 取得したデータ(JSONなど)
        // if ($json_data) {
        //     $statuses = $json_data['statuses']; //ステータス情報取得
        // }
        dd(json_decode($json_data));

        // if ($statuses && is_array($statuses)) {
        //     $sts_cnt = count($statuses);
        //     // 一番古いデータからDBへ書き込む
        //     for ($i = $sts_cnt - 1; $i >= 0; $i--) {
        //         $result = $statuses[$i];
        //         $has_media = true;
        //         $screen_name = $result['user']['screen_name'];
        //         $twitter_id = $result['user']['id_str'];

        //         //$cnt++;
        //         $tw_created_date = date('Y-m-d H:i:s', strtotime($result["created_at"]));
        //         $user_name      = $result['user']['name'];
        //         $tweet_id       = $result['id_str'];
        //         $profile_url    = $result['user']['profile_image_url'];
        //         $tweet_text     = $result['text'];

        //         $img_src = $short_url = $display_url = "";

        //         if (isset($result["entities"]['media'])) { //写真等がある場合、取得（ビデオリンクの場合、違う方法で取得可能）
        //             // 最初のメディアのみを取得する（全部取得できるように修正を)
        //             $img_src        = $result["entities"]['media'][0]['media_url'];
        //             $short_url      = $result["entities"]['media'][0]['url'];
        //             $display_url    = $result["entities"]['media'][0]['display_url'];
        //         }

        //         // DBへデータを書き込む処理
        //         writeToDatabase();
        //     }
        // }
    }
}
