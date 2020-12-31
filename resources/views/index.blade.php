@extends('layouts.app')

@section('content')
  <div class="container c-container__fluid text-center mt-5">
    <div class="p-guest_wrapper mt-5">
      <h3 class="mb-4">仮想通貨のトレンドを素早くキャッチ！</h3>

      <p>CryptoTrendでは、18種類の仮想通貨それぞれのツイートを分析し、仮想通貨のトレンドを素早くご提供します。</p>
      <p>仮想通貨に関連するTwitterアカウントを一括フォロー</p>
        関する最新ニュースをまとめてチェックできます。</p>
  
      <div class="py-3">
        <a href="{{ route('register') }}" class="btn btn-dark">ユーザー登録</a>
      </div>
      <div class="py-3">
        <a href="{{ route('login') }}" class="btn btn-dark">ログイン</a>
      </div>
    </div>
  </div>
@endsection
