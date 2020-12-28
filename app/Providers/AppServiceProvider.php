<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // MySQL5.7.7未満では、ユニーク制約を付けたカラムは最大767bytesなので
        // varchar(191) * 4bytes(utf8mb4) = 764bytes となるよう
        // varcharのデフォルト文字数を変更。
        Schema::defaultStringLength(191);

        // 商用環境以外の場合、storage/logs/の中にSQLログを出力する
        if (config('app.env') !== 'production') {
            DB::listen(function ($query) {
                \Log::info("Query Time:{$query->time}s] $query->sql");
            });
        }
    }
}
