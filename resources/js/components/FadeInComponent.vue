<template>
  <div :class="{ 'c-fade--in': isVisible }">
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
