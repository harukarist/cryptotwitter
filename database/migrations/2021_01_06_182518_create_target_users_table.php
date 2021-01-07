<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('twitter_id')->unique();
            $table->string('user_name');
            $table->string('screen_name');
            $table->integer('follow_num');
            $table->integer('follower_num');
            $table->text('profile_text');
            $table->string('profile_img');
            $table->string('tweet_id')->nullable();
            $table->string('tweet_text')->nullable();
            $table->dateTime('tweeted_at')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * 
     */
    public function down()
    {
        Schema::dropIfExists('target_users');
    }
}
