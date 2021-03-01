<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsListsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = file_get_contents(base_path() . '/database/sql/news_lists.sql');
        DB::unprepared($sql);
    }
}
