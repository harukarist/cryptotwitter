<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExistUniqueToTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            // twitter_idカラムとexistカラムの複合ユニーク制約を設定
            // （ユーザーのTwitterアカウントレコードが論理削除されるとexistカラムがNULLになるため、複合ユニーク制約が無効となり、同一のtwitter_idが再登録可能になる）
            $table->unique(['twitter_id', 'exist']);
            // user_idカラムとexistカラムの複合ユニーク制約を設定
            $table->unique(['user_id', 'exist']);
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
            // twitter_idカラムとexistカラムの複合ユニーク制約を削除
            $table->dropUnique(['twitter_id', 'exist']);
            // user_idカラムとexistカラムの複合ユニーク制約を削除
            $table->dropUnique(['user_id', 'exist']);
        });
    }
}
