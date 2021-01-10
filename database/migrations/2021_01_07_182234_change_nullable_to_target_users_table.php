<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNullableToTargetUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_users', function (Blueprint $table) {
            $table->integer('follow_num')->nullable()->change();
            $table->integer('follower_num')->nullable()->change();
            $table->text('profile_text')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('target_users', function (Blueprint $table) {
            $table->integer('follow_num')->nullable(false)->change();
            $table->integer('follower_num')->nullable(false)->change();
            $table->text('profile_text')->nullable(false)->change();
        });
    }
}
