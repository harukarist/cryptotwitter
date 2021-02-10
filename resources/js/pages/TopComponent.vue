<template>
  <div class="c-container--fluid">
    <div class="p-hero" ref="hero">
      <div class="p-hero__wrapper">
        <div class="p-hero__contents">
          <h2 class="p-hero__title c-fade--in">
            仮想通貨のトレンドを<br />
            素早くキャッチ！
          </h2>
          <p class="p-hero__text c-fade--in">
            仮想通貨のトレンド分析、自動フォロー、<br />
            最新ニュースのチェックをサポートします
          </p>

          <div class="p-hero__action c-fade--in">
            <RouterLink
              :to="{ name: 'register' }"
              class="c-btn__white p-hero__btn"
            >
              今すぐ無料ではじめる
            </RouterLink>
            <RouterLink :to="{ name: 'login' }" class="p-hero__link">
              アカウントをお持ちの方はこちら
            </RouterLink>
          </div>
        </div>
        <div class="p-hero__img-wrapper">
          <img src="/img/img_hero.png" class="p-hero__img" alt="CryptoTrend" />
        </div>
      </div>
    </div>

    <div id="about" ref="about">
      <AboutComponent />
    </div>
    <TroubleComponent />
    <SolutionComponent />
    <ActionComponent />
    <div id="reason" ref="reason">
      <ReasonComponent />
    </div>
    <div id="faq" ref="faq">
      <FaqComponent />
    </div>
    <ActionComponent />
  </div>
</template>

<script>
import FadeInComponent from "../components/FadeInComponent.vue";
import AboutComponent from "../components/top/AboutComponent.vue";
import TroubleComponent from "../components/top/TroubleComponent.vue";
import SolutionComponent from "../components/top/SolutionComponent.vue";
import ReasonComponent from "../components/top/ReasonComponent.vue";
import FaqComponent from "../components/top/FaqComponent.vue";
import ActionComponent from "../components/top/ActionComponent.vue";

export default {
  components: {
    FadeInComponent,
    AboutComponent,
    TroubleComponent,
    SolutionComponent,
    ReasonComponent,
    FaqComponent,
    ActionComponent,
  },
  data() {
    return {
      currentHeight: 0,
      heroHeight: 0,
      hash: this.$route.hash, //URL中のハッシュを取得
    };
  },
  methods: {
    // hashからアンカーポイントまでスクロール
    scrollToAnchorPoint(refName) {
      const el = this.$refs[refName];
      el.scrollIntoView({ behavior: "smooth" });
    },
  },
  computed: {
    getHeight() {
      document.onscroll = (e) => {
        this.currentHeight =
          document.documentElement.scrollTop || document.body.scrollTop;
      };
    },
  },
  mounted() {
    this.heroHeight = this.$refs.hero.clientHeight; //ref属性heroのDOM要素の高さを取得

    // document.onscroll = (e) => {
    //   this.currentHeight =
    //     document.documentElement.scrollTop || document.body.scrollTop;
    // };

    this.$nextTick(function () {
      //this.$nextTick()でDOMの読み込み完了時に実行
      if (this.hash) {
        const refName = this.hash.replace("#", "");
        console.log(refName);
        this.scrollToAnchorPoint(refName);
      }
    });
  },
  watch: {
    // watchプロパティでisFixedの値の変更を監視
    getHeight: {
      async handler(val) {
        if (this.currentHeight > this.heroHeight) {
          this.$emit("isFixed");
          console.log("emit");
        }
      },
    },
  },
};
</script>
