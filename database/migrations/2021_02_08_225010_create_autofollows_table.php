<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autofollows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('twitter_user_id')->unsigned();
            $table->bigInteger('target_id')->unsigned();
            $table->timestamps();

            // 外部キーを設定
            $table->foreign('twitter_user_id')
                ->references('id')->on('twitter_users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('target_id')
                ->references('id')->on('target_users')
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
        Schema::dropIfExists('autofollows');
    }
}
