<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('twitter_id')->unsigned()->unique();
            $table->bigInteger('user_id')->unsigned()->unique();
            $table->string('twitter_token');
            $table->string('twitter_token_secret');
            $table->string('user_name');
            $table->string('screen_name');
            $table->text('twitter_avatar');
            $table->boolean('use_autofollow')->default(false);
            $table->timestamps();

            // 外部キーを設定
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            // ソフトデリートを定義
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitter_users');
    }
}
