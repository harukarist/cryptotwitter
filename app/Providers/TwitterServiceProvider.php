<?php

namespace App\Providers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\ServiceProvider;

// TwitterOAuthを提供するためのサービスプロバイダ
class TwitterServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    // deferプロパティにtrueをセットし、プロバイダのローディングを必要になるまで遅延する
    protected $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    // サービスコンテナにTwitterOAuthクラスのインスタンスを'twitter'として登録
    public function register()
    {
        // singleton()を通じて登録することで、インスタンスを一度だけ生成する
        $this->app->singleton('twitter', function () {

            // ヘルパー関数config()でconfig/twitter.phpを参照してインスタンスを作成
            $config = config('twitter');

            // 認証情報をセットしてTwitterOAuthのインスタンスを返却
            return new TwitterOAuth($config['api_key'], $config['secret_key'], $config['access_token'], $config['access_token_secret']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    // 登録したサービスコンテナ名を返す
    public function provides()
    {
        return ['twitter'];
    }
}
