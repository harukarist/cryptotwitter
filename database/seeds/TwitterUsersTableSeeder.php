<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TwitterUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = DB::table('users')
            ->where('email', 'kumitrend@gmail.com')->first();

        DB::table('twitter_users')->insert([
            'twitter_id' => '1363103456077094914',
            'user_id' => $user->id,
            'twitter_token' => '1363103456077094914-8q3HDUdhcTj2ssFxcRPZ0dLtBgz2uh',
            'twitter_token_secret' => 'WnDpXjxrmcwq8lo1XnEfOiSAASRTCN7Qigedf4lugSd7T',
            'user_name' => 'kumi',
            'screen_name' => 'kumitrend',
            'twitter_avatar' => 'https://pbs.twimg.com/profile_images/1363151828620206085/zbFYYaMR_normal.jpg',
            'use_autofollow' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
