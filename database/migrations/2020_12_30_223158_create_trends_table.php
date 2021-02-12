<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency_name')->unique();
            $table->string('currency_ja')->nullable();
            $table->string('currency_pair')->nullable();
            $table->unsignedInteger('use_api')->default(0);
            $table->double('high')->default(0);
            $table->double('low')->default(0);
            $table->unsignedBigInteger('tweet_hour')->default(0);
            $table->unsignedBigInteger('tweet_day')->default(0);
            $table->unsignedBigInteger('tweet_week')->default(0);
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
        Schema::dropIfExists('trends');
    }
}
