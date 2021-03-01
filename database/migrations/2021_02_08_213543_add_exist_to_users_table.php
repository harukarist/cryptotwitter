<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExistToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // レコードが論理削除されている（deleted_atに日付データがある）場合は NULL， 論理削除されていなければ 1 になるexistカラムを追加
            $table->boolean('exist')->nullable()->storedAs('case when deleted_at is null then 1 else null end');

            // emailカラムとexistカラムの複合ユニーク制約を設定
            // （ユーザーレコードが論理削除されるとexistカラムがNULLになるため、複合ユニーク制約が無効となり、同一のemailが再登録可能になる）
            $table->unique(['email', 'exist']);
            // emailカラムのユニーク制約を削除
            $table->dropUnique('users_email_unique');
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
            // emailカラムとexistカラムの複合ユニーク制約を削除
            $table->dropUnique(['email', 'exist']);
            // existカラムを削除
            $table->dropColumn('exist');
            // emailカラムのユニーク制約を設定
            $table->unique('email');
        });
    }
}
