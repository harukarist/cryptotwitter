<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\curlRequestController;

/**
 * 「TwproAPI」でキーワードを含む仮想通貨関連Twitterアカウントを取得し、DBに保存するコマンド
 */
class FetchTwpro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:twpro';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'TwproAPIから仮想通貨アカウントを取得し、DBに保存';

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
        // 仮想通貨アカウントの検索キーワード
        $KEYWORDS = ['仮想通貨', '暗号資産', 'ビットコイン'];

        logger()->info('>>>> TwproTwitterユーザー保存バッチを実行します');
        foreach ($KEYWORDS as $keyword) {
            // TwproAPIのリクエスト残り回数を取得
            $limit = $this->checkTwproLimit();

            $limit_count = $limit->rest; //残りAPI利用可能回数
            logger()->info("リクエスト回数はあと" . $limit_count . "回");

            $until = $limit->until; //次にrest値が復帰する日時(Unix時間)

            // リクエスト残り回数が0の場合
            if ($limit_count <= 0) {
                dump("Twproのリクエスト上限に達しました");
                logger()->info("Twproのリクエスト上限に達しました");
                // 利用再開時刻が指定されている場合
                if ($until) {
                    $date = date('Y/m/d H:i:s', $until);
                    dump("利用再開は{$date}以降");
                    logger()->info("利用再開は{$date}以降");
                    // 指定の時刻まで処理を遅延する
                    time_sleep_until($until);
                    dump("Twproのリクエストを再開");
                    logger()->info("Twproのリクエストを再開");
                }
            }

            // ユーザー検索オプションを指定
            $params = [
                'q' => $keyword, //親コントローラーで指定したキーワード
                'num' => 300, //取得ユーザー数（最大300件）
            ];

            dump("「{$keyword}」でTwproからTwitterアカウントを検索");
            logger()->info("「{$keyword}」でTwproからTwitterアカウントを検索");

            // TwproAPIでユーザーを検索
            $users_json = $this->requestUsersByTwpro($params);
        }

        logger()->info('TwproTwitterユーザー保存バッチを実行しました <<<<');

        // 最新ツイートが取得できなかったTwitterアカウントの最新ツイートをTwitterAPIから取得
        Artisan::call('fetch:targetsTweet');
    }


    /**
     * TwproAPIでレートリミットを取得するメソッド
     */
    public function checkTwproLimit()
    {
        // レートリミット取得エンドポイントのURLを指定
        $url = 'https://twpro.jp/1/limit';

        // cURLで該当URLのAPIから情報を取得するメソッドを実行し、結果を返却
        $limit = curlRequestController::curl($url);
        return $limit;
    }

    /**
     * TwproAPIでキーワードを含むTwitterアカウントを検索・取得するメソッド
     */
    public function requestUsersByTwpro($params)
    {
        $create_total = 0;
        $update_total = 0;
        $req_count = 1;
        // 検索パラメーターの配列をクエリ文字列に変換
        $query = http_build_query($params);
        // Twitterアカウント検索エンドポイントのリクエストURLを生成
        $url = 'http://api.twpro.jp/1/search?' . $query;

        // cURLで該当URLのAPIから情報を取得するメソッドを実行し、キーワードを含むTwitterアカウントを検索
        $users_json = curlRequestController::curl($url);

        // TwproAPIから返却された検索結果の件数をカウント
        $users_count = $users_json->count;
        // 検索結果がある場合
        if ($users_count) {
            dump("Twproからユーザー情報を{$users_count}件取得");
            logger()->info("Twproからユーザー情報を{$users_count}件取得");

            // 検索結果から必要なデータを抽出し、DBに保存
            $count = $this->createRecord($users_json->users);
            // 新規保存したレコード件数、更新したレコード件数を加算
            $create_total += $count['create'];
            $update_total += $count['update'];
        } else {
            dump("検索結果は0件でした");
            logger()->info("検索結果は0件でした");
        }

        dump("{$create_total}件のユーザー情報を新規保存しました");
        dump("{$update_total}件のユーザー情報を更新しました");
        logger()->info("{$create_total}件のユーザー情報を新規保存しました");
        logger()->info("{$update_total}件のユーザー情報を更新しました");
        logger()->info("{$req_count}回リクエスト済み");

        // ユーザー取得ログをDBに保存
        DB::table('fetch_targets_logs')->insert([
            'create_total' => $create_total,
            'update_total' => $update_total,
            'request_count' => $req_count,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return;
    }

    /**
     * 検索結果からTwitterアカウント情報を取り出し、DBに保存するメソッド
     */
    public function createRecord($users_arr)
    {
        $create_count = 0;
        $update_count = 0;

        $query = [];
        // 検索結果がある場合
        if ($users_arr) {
            // 取得したユーザー件数分ループを回し、必要なデータを配列に格納
            foreach ($users_arr as $user) {
                $query = [
                    'twitter_id' => $user->id,  //ユーザーID
                    'user_name' => $user->name, //ユーザー名
                    'screen_name' => $user->screen_name, //@から始まるアカウント名
                    'follow_num' => $user->friends_count, //フォロー数
                    'follower_num' => $user->followers_count, //フォロワー数
                    'profile_text' => $user->description, //プロフィール文章
                    'profile_img' => $user->profile_image_url_https, //プロフィールアイコンURL
                    'url' => $user->url, //URL
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                // 該当のTwitterIDを持つレコードをテーブルから1件取得
                $target = DB::table('target_users')->where('twitter_id', $user->id)->first();
                if (!$target) {
                    // レコードがなければ新規作成
                    DB::table('target_users')->insert($query);
                    $create_count++;
                } else {
                    // レコードがあれば更新
                    DB::table('target_users')->where('twitter_id', $user->id)->update($query);
                    $update_count++;
                }
            }
            // 新規保存したレコード件数、更新したレコード件数を返却
            $count['create'] = $create_count; // 新規保存したレコード件数
            $count['update'] = $update_count; // 更新したレコード件数
            return $count;
        }

        return;
    }
}
