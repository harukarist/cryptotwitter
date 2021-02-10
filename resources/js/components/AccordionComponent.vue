<template>
  <div class="js-accordion c-accordion">
    <div
      class="c-accordion__title"
      :class="{ 'is-opened': isOpened, 'is-hovered': isHovered }"
      @click="toggleAccordion()"
      @mouseover="isHovered = true"
      @mouseleave="isHovered = false"
    >
      <slot name="title"></slot>
    </div>

    <transition
      name="js-accordion"
      @before-enter="beforeEnter"
      @enter="enter"
      @before-leave="beforeLeave"
      @leave="leave"
    >
      <div
        v-if="isOpened"
        class="js-accordion__target c-accordion__body"
        :class="{ 'is-show': isOpened }"
      >
        <slot name="body"></slot>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpened: false,
      isHovered: false,
    };
  },
  methods: {
    toggleAccordion() {
      this.isOpened = !this.isOpened;
    },
    beforeEnter: function (el) {
      el.style.height = "0";
    },
    enter: function (el) {
      el.style.height = el.scrollHeight + "px";
    },
    beforeLeave: function (el) {
      el.style.height = el.scrollHeight + "px";
    },
    leave: function (el) {
      el.style.height = "0";
    },
  },
};
</script>

<style lang="scss" scoped>
.js-accordion {
  &__target {
    transition: height 0.3s ease-in-out;
  }
  &-enter-active {
    animation-duration: 1s;
    animation-fill-mode: both;
    animation-name: openAccordion;
  }
  &-leave-active {
    animation-duration: 1s;
    animation-fill-mode: both;
    animation-name: closeAccordion;
  }
}

@keyframes openAccordion {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
@keyframes closeAccordion {
  0% {
    opacity: 1;
  }

  100% {
    opacity: 0;
  }
}
</style>
