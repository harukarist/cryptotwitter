<template>
  <transition
    name="popup"
    appear>
    <div
      v-if="isVisible"
      class="p-pagetop"
      @click="scrollTop()">
      <span class="p-pagetop__btn"><i class="fas fa-chevron-up" /></span>
    </div>
  </transition>
</template>

<script>
export default {
  data() {
    return {
      currentHeight: 0,
    };
  },
  computed: {
    // スクロール量が200pxを超えたらページトップへ戻るボタンを表示
    isVisible() {
      return this.currentHeight > 200;
    },
  },
  mounted() {
    document.onscroll = () => {
      // 現在のスクロール量を取得
      this.currentHeight =
        document.documentElement.scrollTop || document.body.scrollTop;
    };
  },
  methods: {
    // ページの最上部までスクロールするメソッド
    scrollTop() {
      // クリックしたらtop:0までスクロール
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    },
  },
};
</script>
