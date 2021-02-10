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
</head>
<body>
    <div id="app">
        <app-component></app-component>

        {{-- <header id="header">
            <header-component />
        </header>
        <main id="main">
            @yield('content')
            <app-component />
        </main>
        <footer id="footer">
            <footer-component />
        </footer> --}}

        <!-- セッション経由のフラッシュメッセージを表示 -->
        @if (session('status'))
            <message-component message="{{ session('status') }}" type="{{ session('type')}}" timeout="{{ session('timeout')}}" />
        @endif
    </div>
</body>
</html>
