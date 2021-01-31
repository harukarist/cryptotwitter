<template>
  <div class="c-container--fluid">
    <div class="p-hero">
      <h2 class="p-hero__title">
        仮想通貨のトレンドを<br />
        素早くキャッチ！
      </h2>
      <div class="p-hero__text-wrap">
        <p class="p-hero__text">
          CryptoTrendでは18種類の仮想通貨それぞれのツイートを分析し、
          <br class="u-sp--hidden" />
          仮想通貨のトレンドを素早くご提供します。
        </p>
        <p class="p-hero__text">
          仮想通貨に興味を持っているTwitterアカウントの一括フォローや、
          <br class="u-sp--hidden" />
          仮想通貨に関する最新ニュースの一覧チェックを簡単に行うことができます。
        </p>
      </div>

      <div class="p-hero__action">
        <RouterLink
          :to="{ name: 'register' }"
          class="c-btn__accent p-hero__btn"
        >
          無料ではじめる
        </RouterLink>
        <RouterLink
          :to="{ name: 'login' }"
          class="c-btn__main-outline p-hero__btn"
        >
          ログイン
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script>
export default {};
</script>
