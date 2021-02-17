<template>
  <div class="p-action">
    <fade-in-component>
      <h3 class="p-action__heading">
        仮想通貨のトレンドを<br class="u-sp--only" />すばやくキャッチ！
      </h3>
    </fade-in-component>

    <fade-in-component>
      <div class="p-action__logo">
        <img
          src="/img/img_logo_white.png"
          class="p-action__logo-img"
          alt="CryptoTrend"
        />
        <h3 class="p-action__logo-text">CryptoTrend</h3>
      </div>
    </fade-in-component>

    <fade-in-component>
      <p class="p-action__copy">\ 30秒で登録完了！ /</p>
    </fade-in-component>

    <fade-in-component>
      <RouterLink
        :to="{ name: 'register' }"
        class="c-btn--white c-btn--large c-btn--arrow"
      >
        今すぐ無料ではじめる
      </RouterLink>
    </fade-in-component>
  </div>
</template>

<script>
import FadeInComponent from "../FadeInComponent.vue";

export default {
  components: { FadeInComponent },
};
</script>
