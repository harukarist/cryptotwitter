<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFetchTweetsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fetch_tweets_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('since_at');
            $table->dateTime('until_at');
            $table->integer('total_count');
            $table->string('begin_id');
            $table->string('end_id')->nullable();
            $table->string('next_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fetch_tweets_logs');
    }
}
