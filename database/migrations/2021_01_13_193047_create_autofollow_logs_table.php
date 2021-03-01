<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutofollowLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autofollow_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('twitter_user_id')->unsigned();
            $table->integer('follow_total');
            $table->integer('remain_num');
            $table->timestamps();

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
        Schema::dropIfExists('autofollow_logs');
    }
}
