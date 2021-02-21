<template>
  <div class="c-container--fluid">
    <hero-component />
    <div
      id="about"
      ref="about"
    >
      <about-component />
    </div>
    <trouble-component />
    <solution-component />
    <action-component />
    <div
      id="reason"
      ref="reason"
    >
      <reason-component />
    </div>
    <div
      id="faq"
      ref="faq"
    >
      <faq-component />
    </div>
    <action-component />
  </div>
</template>

<script>
import HeroComponent from "../components/top/HeroComponent.vue";
import AboutComponent from "../components/top/AboutComponent.vue";
import TroubleComponent from "../components/top/TroubleComponent.vue";
import SolutionComponent from "../components/top/SolutionComponent.vue";
import ReasonComponent from "../components/top/ReasonComponent.vue";
import FaqComponent from "../components/top/FaqComponent.vue";
import ActionComponent from "../components/top/ActionComponent.vue";

export default {
  components: {
    HeroComponent,
    AboutComponent,
    TroubleComponent,
    SolutionComponent,
    ReasonComponent,
    FaqComponent,
    ActionComponent,
  },
  data() {
    return {
      //VueRouterで取得したURL中のハッシュを取得
      hash: this.$route.hash,
    };
  },
  watch: {
    // $routeを監視し、トップページを表示時にヘッダーのトップページリンクがクリックされたら
    // そのアンカーポイントへ移動
    $route(to) {
      this.pageScroll(to.hash);
    },
  },
  mounted() {
    //this.$nextTick()でDOMの読み込み完了時に実行
    this.$nextTick(function () {
      // URL中にhashがある場合
      if (this.hash) {
        this.pageScroll(this.hash);
        // // #を除いた文字列を取得
        // const refName = this.hash.replace("#", "");
        // // this.scrollToAnchorPoint(refName);
        // setTimeout(() => {
        //   // 正しい高さを取得できるよう、画像の読み込みを待ってからスクロールメソッドを実行
        //   this.scrollToAnchorPoint(refName);
        // }, 100);
      }
    });
  },
  methods: {
    pageScroll(hash) {
      // #を除いた文字列を取得
      const refName = hash.replace("#", "");
      setTimeout(() => {
        // 正しい高さを取得できるよう、画像の読み込みを待ってからスクロールメソッドを実行
        this.scrollToAnchorPoint(refName);
      }, 50);
    },
    // アンカーポイントまでスクロールするメソッド
    scrollToAnchorPoint(refName) {
      // refNameと一致するref属性を持つ要素を取得
      const el = this.$refs[refName];
      // その要素にスクロール
      el.scrollIntoView({ behavior: "smooth" });
      this.hash = "";
    },
  },
};
</script>
