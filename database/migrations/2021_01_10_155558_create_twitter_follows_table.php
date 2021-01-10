<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitterFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_follows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('twitter_id')->unsigned();
            $table->bigInteger('target_id')->unsigned();
            $table->timestamps();

            // 外部キーを設定
            $table->foreign('twitter_id')
                ->references('twitter_id')->on('twitter_users')
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
        Schema::dropIfExists('twitter_follows');
    }
}
