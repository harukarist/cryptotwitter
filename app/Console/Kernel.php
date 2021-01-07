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
    // 作成したCommandを登録する
    protected $commands = [
        Commands\UpdatePrices::Class,
        Commands\FetchTweets::Class,
        Commands\FetchUsers::Class,
        Commands\FetchTwpro::Class,
        Commands\UpdateTweets::Class,
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
        // $schedule->command('inspire')
        //          ->hourly();

        // everyHour()で1時間毎に仮想通貨の価格チェックを実行する
        // hourlyAt()で毎時5分に仮想通貨の価格チェックを実行する
        $schedule->command('update:prices')
            ->hourlyAt(5);
        // everyFifteenMinutes()で15分毎にツイート検索を実行する
        $schedule->command('fetch:tweets')
            ->everyFifteenMinutes();
        // daily()で毎日深夜12時にTwitterアカウントの検索を実行する
        // $schedule->command('fetch:users')
        //     ->daily();
        $schedule->command('fetch:users')
            ->everyFiveMinutes();


        $schedule->command('fetch:twpro')
            ->everyMinute();

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
