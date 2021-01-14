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
        Commands\FetchTweets::Class,
        Commands\FetchTwpro::Class,
        Commands\FetchUsers::Class,
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
        // hourlyAt()で毎時5分に仮想通貨の価格チェックを実行する
        $schedule->command('update:prices')
            ->hourlyAt(5);
        // everyFifteenMinutes()で15分毎に自動フォローを実行する
        $schedule->command('follow:autofollow')
            ->everyFifteenMinutes();

        // everyFifteenMinutes()で15分毎にツイート検索を実行する
        $schedule->command('fetch:tweets')
            ->everyFifteenMinutes();

        // dailyAt('01:00')で毎日深夜1:00にアカウント一覧を更新する
        $schedule->command('fetch:users')
            ->dailyAt('01:00');
        $schedule->command('fetch:twpro')
            ->dailyAt('01:00');

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
