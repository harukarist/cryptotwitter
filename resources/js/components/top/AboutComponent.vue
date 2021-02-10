<template>
  <div class="p-about">
    <fade-in-component>
      <img
        src="/img/img_logo_white.png"
        class="p-about__logo-img"
        alt="CryptoTrend"
      />
      <h3 class="p-about__title">
        <span class="u-font__large">CryptoTrend</span>とは
      </h3>
      <p class="p-about__text">
        数多くの銘柄が存在し、<br
          class="u-sp--only"
        />変化の激しい仮想通貨市場で<br />
        効率よく情報収集が行えるよう、<br
          class="u-sp--only"
        />あなたをサポートするサービスです。
      </p>
    </fade-in-component>
  </div>
</template>

<script>
import FadeInComponent from "../FadeInComponent.vue";

export default {
  components: { FadeInComponent },
};
</script>
