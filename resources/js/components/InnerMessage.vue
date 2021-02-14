<template>
  <transition name="slide">
    <div
      v-if="flashMessage"
      class="c-alert p-flash"
      :class="`c-alert--${messageType}`"
    >
      <p class="p-flash__text">{{ flashMessage }}</p>
      <i class="fas fa-times p-flash__close" @click="closeMsgBox()"></i>
    </div>
  </transition>
</template>

<script>
export default {
  computed: {
    flashMessage() {
      return this.$store.state.message.text;
    },
    messageType() {
      return this.$store.state.message.type;
    },
  },
  methods: {
    closeMsgBox() {
      this.$store.commit("message/clearMessage");
    },
  },
};
</script>
