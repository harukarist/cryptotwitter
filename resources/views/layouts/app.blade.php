<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CryptoTrend') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Google Fonts -->
    <!-- dns-prefetchでページの読み込みと並行してGoogleFonts APIに接続 -->
    <!-- preconnectで複数のリクエストを同時に実行 -->
    <link rel="dns-prefetch preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+JP:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/android-chrome-256x256.png">
    
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/f2d7c28546.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- OGP -->
    <meta property="og:title" content="CryptoTrend">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://crypto-trend.harukarist.com/">
    <meta property="og:image" content="{{ asset('img/ogp.png') }}">
    <meta property="og:site_name" content="CryptoTrend">
    <meta property="og:description" content="CryptoTrendは仮想通貨のTwitterトレンド分析、Twitterアカウントの自動フォロー、最新ニュースのチェックをサポートする無料サービスです">
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@cryptotrendjp" />
    <meta name="twitter:player" content="@cryptotrendjp" />

</head>
<body>
    <div id="app">
        <header id="header">
            <header-component></header-component>
        </header>
        @yield('content')
        <app-component></app-component>
        <!-- セッション経由のフラッシュメッセージを表示 -->
        @if (session('message'))
        <message-component message="{{ session('message') }}" type="{{ session('type') ?? 'success' }}" timeout="{{ session('timeout') ?? 3000 }}" />
        @endif
        
    </div>
</body>
</html>
