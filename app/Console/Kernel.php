<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * バッチ処理を指定のタイミングで実行するためのタスクスケジューラー
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // バッチ処理を実行するCommandクラスをコマンド変数に登録する
    protected $commands = [
        Commands\AutoFollow::Class,
        Commands\DeleteOldRecords::Class,
        Commands\FetchNews::Class,
        Commands\FetchTargets::Class,
        Commands\FetchTargetsTweet::Class,
        Commands\FetchTweetsLatest::Class,
        Commands\FetchTweetsWeekly::Class,
        Commands\FetchTwpro::Class,
        Commands\UpdatePrices::Class,
        Commands\UpdateTweetCount::Class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    // Commandを定期的に実行するタスクスケジュールを設定するメソッド
    protected function schedule(Schedule $schedule)
    {
        // 15分毎に自動フォローを行うコマンドを実行
        $schedule->command('follow:autofollow')
            ->everyFifteenMinutes();

        // 10分毎にTwitterAPIで仮想通貨関連ツイートを取得するコマンドを実行
        $schedule->command('fetch:latestTweets')
            ->everyTenMinutes();
        // 仮想通貨関連ツイート取得後、各銘柄のツイート数を集計するコマンドを実行
        $schedule->command('update:tweetCount')
            ->everyTenMinutes();

        // 10分毎に仮想通貨銘柄の価格を更新するコマンドを実行
        $schedule->command('update:prices')
            ->everyTenMinutes();

        // 15分毎にGoogleニュースを取得するコマンドを実行
        $schedule->command('fetch:news')
            ->everyFifteenMinutes();

        // 毎日深夜1:00にTwitterAPIで仮想通貨アカウントを取得するコマンドを実行
        $schedule->command('fetch:targets')
            ->dailyAt('01:00');
        $schedule->command('fetch:twpro')
            ->dailyAt('01:00');
        // Twproでの仮想通貨アカウント取得後に、未取得の最新ツイートを取得するコマンドを実行
        $schedule->command('fetch:targetsTweet')
            ->dailyAt('01:10');

        // 毎日深夜2:13に古いツイート及び取得ログレコードを削除するコマンドを実行
        $schedule->command('delete:records')
            ->dailyAt('02:13');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
