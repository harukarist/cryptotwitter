<template>
  <div class="pagination">
    <RouterLink
      v-if="!isFirstPage"
      :to="`/${directory}?page=${currentPage - 1}`"
      class="button"
      >&laquo; prev</RouterLink
    >
    <RouterLink
      v-if="!isLastPage"
      :to="`/${directory}?page=${currentPage + 1}`"
      class="button"
      >next &raquo;</RouterLink
    >
  </div>
</template>

<script>
export default {
  props: {
    // 現在ページ
    currentPage: {
      type: Number,
      required: true,
    },
    // 最終ページ数
    lastPage: {
      type: Number,
      required: true,
    },
    // ディレクトリ
    directory: {
      type: String,
      required: false,
    },
  },
  computed: {
    // 1ページ目かどうか
    isFirstPage() {
      return this.currentPage === 1;
    },
    // 最終ページかどうか
    isLastPage() {
      return this.currentPage === this.lastPage;
    },
  },
};
</script>
