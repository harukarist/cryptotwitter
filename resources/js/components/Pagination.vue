<template>
  <div>
    <div>
      <ul class="c-pagination">
        <li class="inactive" v-if="!isFirstPage">
          <RouterLink :to="`/${directory}?page=${currentPage - 1}`">
            «
          </RouterLink>
        </li>
        <li
          v-for="page in frontPageRange"
          :key="page"
          :class="isCurrent(page) ? 'active' : 'inactive'"
        >
          <RouterLink :to="`/${directory}?page=${page}`">
            {{ page }}
          </RouterLink>
        </li>
        <li v-show="frontDot" class="inactive disabled">...</li>
        <li
          v-for="page in middlePageRange"
          :key="page"
          :class="isCurrent(page) ? 'active' : 'inactive'"
        >
          <RouterLink :to="`/${directory}?page=${page}`">
            {{ page }}
          </RouterLink>
        </li>
        <li v-show="endDot" class="inactive disabled">...</li>
        <li
          v-for="page in endPageRange"
          :key="page"
          :class="isCurrent(page) ? 'active' : 'inactive'"
        >
          <RouterLink :to="`/${directory}?page=${page}`">
            {{ page }}
          </RouterLink>
        </li>
        <li class="inactive" v-if="!isLastPage">
          <RouterLink :to="`/${directory}?page=${currentPage + 1}`">
            »
          </RouterLink>
        </li>
      </ul>
    </div>
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
  data() {
    return {
      range: 5, //ページ数を表示する幅
      size: 5, //省略表示しない最大個数
      frontDot: false,
      endDot: false,
    };
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
    // ページネーション前半の描画
    frontPageRange() {
      if (!this.sizeCheck) {
        return this.calRange(1, this.lastPage);
      }
      return this.calRange(1, 2);
    },
    // ページネーション中間の描画
    middlePageRange() {
      if (!this.sizeCheck) return [];
      let start = "";
      let end = "";
      if (this.currentPage <= this.range) {
        start = 3;
        end = this.range + 2;
        this.frontDot = false;
        this.endDot = true;
      } else if (this.currentPage > this.lastPage - this.range) {
        start = this.lastPage - this.range - 1;
        end = this.lastPage - 2;
        this.frontDot = true;
        this.endDot = false;
      } else {
        start = this.currentPage - Math.floor(this.range / 2);
        end = this.currentPage + Math.floor(this.range / 2);
        this.frontDot = true;
        this.endDot = true;
      }
      return this.calRange(start, end);
    },
    // ページネーション後半の描画
    endPageRange() {
      if (!this.sizeCheck) return [];
      return this.calRange(this.lastPage - 1, this.lastPage);
    },
    // ページ数を省略表示するかを判定
    sizeCheck() {
      if (this.lastPage < this.size) {
        return false;
      }
      return true;
    },
  },
  methods: {
    // 引数で指定した開始値から終了値までの配列を生成
    calRange(start, end) {
      const range = [];
      for (let i = start; i <= end; i++) {
        range.push(i);
      }
      return range;
    },
    // 現在表示しているページかを判別
    isCurrent(page) {
      return page === this.currentPage;
    },
  },
};
</script>

<style lang="scss" scoped>
.c-pagination {
  display: flex;
  list-style-type: none;
}
.c-pagination li {
  border: 3px solid #ddd;
  padding: 6px 12px;
  text-align: center;
}
.active {
  background: blue;
}
.c-pagination li + li {
  border-left: none;
}
.disabled {
  cursor: not-allowed;
}
</style>
