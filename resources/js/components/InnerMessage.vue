<template>
  <transition name="slide-item">
    <div
      v-if="flashMessage"
      class="c-message"
      :class="`c-message--${messageType}`"
    >
      <p class="c-message__text">{{ flashMessage }}</p>
      <i class="fas fa-times c-message__close" @click="closeMsgBox()"></i>
    </div>
  </transition>
</template>

<script>
// VuexのmapState関数をインポート
import { mapState } from "vuex";

export default {
  computed: {
    // ...mapState({
    //   flashMessage: (state) => state.message.text,
    //   messageType: (state) => state.message.type,
    // }),
    flashMessage() {
      return this.$store.state.message.text;
    },
    messageType() {
      return this.$store.state.message.type;
    },
  },
  methods: {
    closeMsgBox() {
      // this.$store.dispatch("message/closeMessage");
      this.$store.commit("message/clearMessage");
    },
  },
};
</script>

<style scoped>
/* フラッシュメッセージ表示アニメーション */
.slide-item-enter-active,
.slide-item-leave-active {
  transition: all 0.5s ease-in-out;
}
/* 表示時 */
.slide-item-enter {
  /* 右からスライドイン */
  opacity: 0;
  transform: translateX(20px);
}
/* 非表示時 */
.slide-item-leave-to {
  /* 右にスライドアウト */
  opacity: 0;
  transform: translateX(20px);
}
</style>
