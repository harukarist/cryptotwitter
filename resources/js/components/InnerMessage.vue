<template>
  <transition name="slide-down">
    <div
      v-if="flashMessage"
      class="c-alert p-message-large"
      :class="`c-alert--${messageType} p-message-large--${messageType}`"
    >
      <p
        class="p-message-large__text"
        :class="`p-message-large__text--${messageType}`"
      >
        {{ flashMessage }}
      </p>
      <i class="fas fa-times p-message-large__close" @click="closeMsgBox()"></i>
    </div>
  </transition>
</template>

<script>
export default {
  computed: {
    flashMessage() {
      // messageストアからフラッシュメッセージを取得
      return this.$store.state.message.text;
    },
    messageType() {
      // messageストアからフラッシュメッセージのcssを取得
      return this.$store.state.message.type;
    },
  },
  methods: {
    // フラッシュメッセージを閉じる
    closeMsgBox() {
      // messageストアのclearMessageミューテーションを実行してメッセージをクリア
      this.$store.commit("message/clearMessage");
    },
  },
};
</script>
