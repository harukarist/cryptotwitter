<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersTableAddTwitterOauthColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('twitter_avatar')->nullable()->after('email')->comment('Twitter アバター画像');
            $table->unsignedBigInteger('twitter_id')->nullable()->after('email')->comment('Twitter ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('twitter_avatar');
            $table->dropColumn('twitter_id');
        });
    }
}
