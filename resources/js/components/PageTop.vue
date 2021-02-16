<template>
  <transition name="popup" appear>
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
    // スクロール量が
    isVisible() {
      return this.currentHeight > 200;
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
