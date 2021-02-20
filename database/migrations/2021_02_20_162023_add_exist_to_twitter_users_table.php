<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExistToTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            // レコードが論理削除されている（deleted_atに日付データがある）場合は NULL， 論理削除されていなければ 1 になるexistカラムを追加
            $table->boolean('exist')->nullable()->storedAs('case when deleted_at is null then 1 else null end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            // existカラムを削除
            $table->dropColumn('exist');
        });
    }
}
