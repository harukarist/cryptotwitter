<template>
  <!-- <transition name="loading"> -->
    <div class="c-loader" v-if="isLoading">
      <div class="c-loader__icon"></div>
    </div>
  <!-- </transition> -->
</template>

<script>
export default {
  computed: {
    isLoading() {
      // console.log("ローディングチェック");
      return this.$store.state.loader.loading;
    },
  },
};
</script>

<style>
.loading-enter-active {
  transition: all 0.25s;
}
.loading-leave-active {
  transition: all 0.5s ease 0.5s; /* ルータービューが終わった後に */
}
</style>
