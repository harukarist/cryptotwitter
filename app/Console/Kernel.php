<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// バッチ処理を行うタスクスケジューラー
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // バッチ処理を実行するCommandクラスを登録する
    protected $commands = [
        Commands\AutoFollow::Class,
        Commands\UpdatePrices::Class,
        Commands\UpdateTweetNum::Class,
        Commands\FetchTweetsLatest::Class,
        Commands\FetchTweetsWeekly::Class,
        Commands\FetchTwpro::Class,
        Commands\FetchTargets::Class,
        Commands\FetchNews::Class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    // Commandを定期的に実行するタスクスケジュールの設定
    protected function schedule(Schedule $schedule)
    {
        // 毎時5分に仮想通貨の価格チェックを実行する
        $schedule->command('update:prices')
            ->hourlyAt(5);
        // 15分毎に自動フォローを実行する
        $schedule->command('follow:autofollow')
            ->everyFifteenMinutes();

        // 10分毎に仮想通貨関連のツイート検索、各銘柄のツイート数の集計を実行する
        $schedule->command('fetch:latestTweets')
            ->everyTenMinutes();
        $schedule->command('update:tweetNum')
            ->everyTenMinutes();

        // 参考：初期導入時など、1週間分のツイートをまとめて保存する場合は以下を実行する
        // $schedule->command('fetch:weeklyTweets')
        //     ->everyFifteenMinutes();

        // 15分毎にニュース検索を実行する
        $schedule->command('fetch:news')
            ->everyFifteenMinutes();

        // 毎日深夜1:00にTwitterアカウント一覧を更新する
        $schedule->command('fetch:targets')
            ->dailyAt('01:00');
        $schedule->command('fetch:twpro')
            ->dailyAt('01:00');

        // 毎日深夜2:13に古いツイート及び取得ログレコードを削除する
        $schedule->command('delete:records')
            ->dailyAt('02:13');

        // daily()で毎日深夜12時に実行する
        // everyHour()で1時間毎に仮想通貨の価格チェックを実行する
        // everyMinute() 毎分
        // everyFiveMinutes()
        // everyTenMinutes()
        // everyFifteenMinutes()
        // everyThirtyMinutes()
        // hourly()
        // hourlyAt(17)
        // daily(17)
        // dailyAt('13:00')
        // dailyAt('13:00')

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
