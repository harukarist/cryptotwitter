<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\FetchTweetsLatest; //FetchTweetsLatestを継承

/**
 * 7日前の0:00から現在時刻の1時間前までの１週間を対象に
 * fetch_tweets_logsテーブルのログレコードを1時間毎に調べ、
 * ツイート未取得の時間帯があれば、ツイートを検索してDBに保存するコマンド。
 * 
 * 親クラスFetchTweetsLatestで最新ツイート取得コマンドを実行した際に
 * 取得ログデータがDBに存在しない場合、または最終ログデータが1時間以上前の場合のみ、
 * 親クラスからこのコマンドが呼び出される。
 * レートリミットの取得、検索用パラメーター生成、ツイート検索、DB保存の処理は
 * 親クラスFetchTweetsLatestのメソッドを継承する。
 */
class FetchTweetsWeekly extends FetchTweetsLatest
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // コマンド名を定義
    protected $signature = 'fetch:weeklyTweets';

    /**
     * The console command description.
     *
     * @var string
     */
    // php artisan listに表示されるコマンドの説明
    protected $description = 'キーワードを含む1週間以内のツイートをTwitterAPIから取得し、DBに保存';

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
        logger()->info('>>>> 1週間のツイート保存バッチを実行します');

        $MAX_REQUEST = 180; // ツイート検索の最大リクエスト回数の初期値（上限は15分間に180回）

        // TwitterAPIのリクエスト残り回数を取得
        $limit_count = $this->checkLimit();

        // リクエスト残り回数が0の場合は処理を終了
        if ($limit_count <= 0) {
            dump("一括ツイート検索のリクエスト上限に達しました");
            logger()->info("一括ツイート検索のリクエスト上限に達しました");
            return;
        }
        // リクエスト残り回数が初期値より少なければ、リクエスト残り回数をリクエスト回数とする
        if ($limit_count < $MAX_REQUEST) {
            $remain_count = $limit_count;
        } else {
            // リクエスト残り回数が初期値より大きければ、初期値をリクエスト回数とする
            $remain_count = $MAX_REQUEST;
        }

        // 今日の0:00の日時を取得
        $dt = Carbon::today();
        // 現在日時を取得
        $carbon_now = Carbon::now();
        // 7日前から処理を開始
        $target_date = $dt->subDays(7);

        // 7日前の0時から1時間毎に未取得のツイートがないかをチェックし、未取得があれば取得、なければ次の時間帯へループ
        for ($add_hours = 0; true; $add_hours++) {

            dump("リクエスト回数はあと" . $remain_count . "回");
            logger()->info("リクエスト回数はあと" . $remain_count . "回");

            // 検索対象とする日時を生成
            $since_at = $target_date->copy()->addHours($add_hours);
            $until_at = $target_date->copy()->addHours($add_hours + 1);

            // 取得対象の開始日時と現在時刻との差が1時間未満の場合は取得しない
            if ($since_at->diffInHours($carbon_now) < 1) {
                break;
            }

            // 対象の開始日時の最新ログレコードがDBにあれば、該当ログを取得
            $log = DB::table('fetch_tweets_logs')
                ->where('since_at', '=', $since_at)
                ->orderBy('id', 'DESC')->first();

            // 対象の開始日時のログレコードがあり、next_id（次回の取得予定ID）が空の場合は全て取得済みのため次の時間帯へ
            if ($log && !($log->next_id)) {
                dump($since_at . "〜" . $until_at . "のツイートは全て取得済み");
                logger()->info($since_at . "〜" . $until_at . "のツイートは全て取得済み");
                continue; //以降の処理は行わずに次のループへ
            }

            // 対象の開始日時のログレコードがない、またはログレコードにnext_id（次回の取得予定ID）がある場合は、対象の開始日時と終了日時を指定して検索パラメータを生成
            $params = $this->getParams($since_at, $until_at);

            // ログレコードにnext_id（次回の取得予定ID）がある場合
            if ($log && $log->next_id) {
                // next_idをAPIパラメータのmax_id（取得開始id)に指定
                $params['max_id'] = $log->next_id;
                dump($log->next_id . "より古いツイートをチェック");
                logger()->info($log->next_id . "より古いツイートをチェック");
            } else {
                // ログレコードがない場合は検索開始IDを指定しない
                dump($since_at . "〜" . $until_at . "の保存データはまだありません");
                logger()->info($since_at . "〜" . $until_at . "の保存データはまだありません");
            }

            // TwitterAPIで対象期間のツイートを検索し、該当データを保存
            [$total_count, $max_id, $req_count] = $this->requestAndSaveTweets($remain_count, $params);

            // 対象時間帯のログをDBに保存して次の時間帯のチェックへ
            DB::table('fetch_tweets_logs')->insert([
                'since_at' => $since_at,
                'until_at' => $until_at,
                'total_count' => $total_count,
                'begin_id' => $log->next_id ?? '',
                'next_id' => $max_id,
                'created_at' => Carbon::now(),
            ]);

            dump($req_count . "回リクエスト済み");
            logger()->info($req_count . "回リクエスト済み");

            // 最大リクエスト回数から今回のリクエスト回数を減らす
            $remain_count -= $req_count;

            // 残り使用可能回数が0以下なら処理を終了
            if ($remain_count <= 0) {
                dump("残り回数が" . $remain_count . "のため処理を終了");
                logger()->info("残り回数が" . $remain_count . "のため処理を終了");
                return;
            }
        }
        return;

        logger()->info('1週間のツイート保存バッチを実行しました <<<<');
    }
}
