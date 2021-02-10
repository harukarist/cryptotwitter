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
    window.addEventListener("scroll", this.handleScroll);
  },
  destroyed() {
    window.removeEventListener("scroll", this.handleScroll);
  },
  methods: {
    handleScroll() {
      if (!this.visible) {
        var top = this.$el.getBoundingClientRect().top;
        this.visible = top < window.innerHeight + 100;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.js-fadeIn {
  animation: fadeIn 2s;
  &__target {
    transition: height 0.3s ease-in-out;
  }
  &-enter-active {
    animation-duration: 2s;
    animation-fill-mode: both;
    animation-name: fadeIn;
  }
  &-leave-active {
    animation-duration: 2s;
    animation-fill-mode: both;
    animation-name: fadeOut;
  }
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
@keyframes fadeOut {
  0% {
    opacity: 1;
    transform: translateY(100px);
  }
  100% {
    opacity: 0;
    transform: translateY(0px);
  }
}
</style>
