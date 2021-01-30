<template>
  <p class="c-pagination__info">
    全{{ totalItems }}件中 {{ startNum }} - {{ endNum }}件を表示
  </p>
</template>

<script>
export default {
  props: {
    // 現在ページ
    currentPage: {
      type: Number,
      required: true,
    },
    //1ページあたりの表示件数
    perPage: {
      type: Number,
      required: true,
    },
    //トータル件数
    totalItems: {
      type: Number,
      required: true,
    },
    //表示中の件数
    itemsLength: {
      type: Number,
      required: true,
    },
  },
  computed: {
    // 表示している最初の件数
    startNum() {
      return this.perPage * (this.currentPage - 1) + 1;
    },
    // 表示している最後の件数
    endNum() {
      return this.perPage * (this.currentPage - 1) + this.itemsLength;
    },
  },
};
</script>
