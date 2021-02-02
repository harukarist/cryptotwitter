<template>
  <transition name="slide-item">
    <div
      v-if="flashMessage && isShow"
      class="c-alert p-flash"
      :class="`c-alert--success`"
    >
      <p class="p-flash__text">{{ flashMessage }}</p>
      <i class="fas fa-times p-flash__close" @click="closeMsgBox()"></i>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    message: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      isShow: true,
    };
  },
  computed: {
    flashMessage() {
      return this.message;
    },
  },
  methods: {
    closeMsgBox() {
      this.isShow = false;
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
