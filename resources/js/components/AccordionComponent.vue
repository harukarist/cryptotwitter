<template>
  <div class="c-accordion">
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
      name="toggle-accordion"
      @before-enter="beforeEnter"
      @enter="enter"
      @before-leave="beforeLeave"
      @leave="leave"
    >
      <div
        v-if="isOpened"
        class="c-accordion__body"
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
    // enter前はheightを0に指定
    beforeEnter: function (el) {
      el.style.height = "0";
    },
    // enter前はheightを要素の高さに指定
    enter: function (el) {
      el.style.height = `${el.scrollHeight}px`;
    },
    // leave前はheightを要素の高さに指定
    beforeLeave: function (el) {
      el.style.height = `${el.scrollHeight}px`;
    },
    // leave前はheightを0に指定
    leave: function (el) {
      el.style.height = "0";
    },
  },
};
</script>
