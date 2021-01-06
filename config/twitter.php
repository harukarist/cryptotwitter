<?php

// abraham/twitteroauth用の設定
return [
  'api_key' => env('TWITTER_API_KEY', ''),
  'secret_key' => env('TWITTER_API_SECRET', ''),
  'access_token' => env('TWITTER_ACCESS_TOKEN', ''),
  'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET', ''),
  'call_back_url' => env('TWITTER_CALLBACK_URL', ''),
];
