<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(TwitterUsersTableSeeder::class);
        $this->call(TrendsTableSeeder::class);
        $this->call(NewsListsSeeder::class);
        $this->call(TargetUsersTableSeeder::class);
    }
}
