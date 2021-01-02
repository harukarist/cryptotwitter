<template>
  <div class="container c-container__fluid text-center mt-5">
    <div class="p-guest_wrapper mt-5">
      <h3 class="mb-4">仮想通貨のトレンドを素早くキャッチ！</h3>

      <p>
        CryptoTrendでは、18種類の仮想通貨それぞれのツイートを分析し、仮想通貨のトレンドを素早くご提供します。
      </p>
      <p>
        仮想通貨に興味を持っているTwitterアカウントの一括フォローや、仮想通貨に関する最新ニュースの一覧チェックを簡単に行うことができます。
      </p>

      <div class="py-3">
        <RouterLink :to="{ name: 'register' }" class="btn btn-dark">
          無料ユーザー登録
        </RouterLink>
      </div>
      <div class="py-3">
        <RouterLink :to="{ name: 'login' }" class="btn btn-dark">
          ログイン
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script>
export default {};
</script>
