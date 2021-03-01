<template>
  <div class="p-hero">
    <div class="p-hero__wrapper">
      <div class="p-hero__contents">
        <h2 class="p-hero__title c-fade-in">
          仮想通貨のトレンドを<br />
          素早くキャッチ！
        </h2>
        <p class="p-hero__text c-fade-in">
          仮想通貨のトレンド分析、自動フォロー、<br />
          最新ニュースのチェックをサポートします
        </p>

        <div class="p-hero__action c-fade-in">
          <router-link
            :to="{ name: 'register' }"
            class="c-btn--white c-btn--large c-btn--arrow u-mb--l"
          >
            今すぐ無料ではじめる
          </router-link>
          <router-link :to="{ name: 'login' }" class="p-hero__link">
            アカウントをお持ちの方はこちら
          </router-link>
        </div>
      </div>
      <div class="p-hero__img-wrapper c-fade-in">
        <img src="/img/img_hero.png" class="p-hero__img" alt="CryptoTrend" />
      </div>
    </div>
  </div>
</template>

<script>
export default {};
</script>
