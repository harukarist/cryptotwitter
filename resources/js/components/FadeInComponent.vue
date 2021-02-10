<template>
  <div :class="{ 'js-fadeIn': visible }">
    <slot v-show="visible"></slot>
  </div>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
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
    handleScroll() {
      if (!this.visible) {
        // ブラウザ表示領域の左上から要素の上端までの値を取得
        var top = this.$el.getBoundingClientRect().top;
        // 要素の上端までの高さがウィンドウ高さ+100pxよりも小さくなったらvisibleをtrueにして表示
        this.visible = top < window.innerHeight + 100;
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
