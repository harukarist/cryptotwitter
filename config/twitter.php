<?php

// Abraham\TwitterOAuthでのOAuth認証に使用する環境変数を指定
return [
  'api_key' => env('TWITTER_API_KEY', ''),
  'secret_key' => env('TWITTER_API_SECRET', ''),
  'access_token' => env('TWITTER_ACCESS_TOKEN', ''),
  'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET', ''),
  'call_back_url' => env('TWITTER_CALLBACK_URL', ''),
];
