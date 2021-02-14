<template>
  <transition
    name="slide-down"
    @before-enter="beforeEnter"
    @enter="enter"
    @before-leave="beforeLeave"
    @leave="leave"
  >
    <slot></slot>
  </transition>
</template>

<script>
export default {
  methods: {
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
