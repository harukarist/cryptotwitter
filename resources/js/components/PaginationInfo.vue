<template>
  <p
    v-if="totalNum"
    class="c-pagination__info"
  >
    全{{ totalNum }}件中 {{ startNum }} - {{ endNum }}件を表示
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
    totalNum: {
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
    // 表示している1つ目の件数
    startNum() {
      // 合計件数が0件の場合は0を返却
      if (this.totalNum === 0) {
        return 0
      }
      // その他の場合は（1ページあたりの表示件数 × 1つ前のページ番号）＋1
      return this.perPage * (this.currentPage - 1) + 1
    },
    // 表示している最後の件数
    endNum() {
      // 合計件数が0件の場合は0を返却
      if (this.totalNum === 0) {
        return 0
      }
      // その他の場合は（1ページあたりの表示件数 × 1つ前のページ番号）＋1
      return this.perPage * (this.currentPage - 1) + this.itemsLength
    },
  },
}
</script>
