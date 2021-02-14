<template>
  <div :class="{ 'js-fadeIn': isVisible }">
    <slot v-show="isVisible"></slot>
  </div>
</template>

<script>
// DOM要素がブラウザ表示領域に入ったらフェードイン表示するコンポーネント
export default {
  data() {
    return {
      isVisible: false,
    };
  },
  created() {
    // スクロールイベントを登録
    window.addEventListener("scroll", this.handleScroll);
  },
  destroyed() {
    // スクロールイベントを解除
    window.removeEventListener("scroll", this.handleScroll);
  },
  methods: {
    // DOM要素がブラウザ表示領域に入ったらisVisibleをtrueにして表示するメソッド
    handleScroll() {
      // 要素が非表示の場合
      if (!this.isVisible) {
        // ブラウザ表示領域の左上から要素の上端までの値を取得
        var top = this.$el.getBoundingClientRect().top;
        // 要素の上端までの高さがウィンドウ高さ+100pxよりも小さくなったら
        // isVisibleをtrueにして表示
        this.isVisible = top < window.innerHeight + 100;
      }
    },
  },
};
</script>

<style scoped>
/* フェードインアニメーション */
.js-fadeIn {
  animation: fadeIn 1s;
}
@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(100px);
  }
  100% {
    opacity: 1;
    transform: translateY(0px);
  }
}
</style>
