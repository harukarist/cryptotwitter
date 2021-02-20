<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueFromTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            // twitter_idカラムのユニーク制約を削除
            $table->dropUnique('twitter_users_twitter_id_unique');
            // user_idカラムのユニーク制約を削除
            $table->dropUnique('twitter_users_user_id_unique');
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
            // twitter_idカラムのユニーク制約を設定
            $table->unique('twitter_id');
            // user_idカラムのユニーク制約を設定
            $table->unique('user_id');
        });
    }
}
