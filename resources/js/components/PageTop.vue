<template>
  <transition name="popup">
    <div class="p-pagetop" v-if="isVisible" @click="scrollTop()">
      <span class="p-pagetop__btn"><i class="fas fa-chevron-up"></i></span>
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
    isVisible() {
      return this.currentHeight > 400;
    },
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
    // visible() {
    //   // ボタンが非表示の場合
    //   if (!this.isVisible) {
    //     this.isVisible = this.currentHeight > window.innerHeight + 100;
    //   }
    // },
    // getHeight() {},
  },
  mounted() {
    document.onscroll = (e) => {
      // 現在のスクロール量を取得
      this.currentHeight =
        document.documentElement.scrollTop || document.body.scrollTop;
    };
  },
};
</script>
