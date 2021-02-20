<template>
  <ul class="c-pagination__list">
    <li class="c-pagination__item" v-show="!isFirstPage">
      <RouterLink :to="linkToFirst">
        <i class="fas fa-angle-double-left"></i>
      </RouterLink>
    </li>
    <li class="c-pagination__item is-disabled" v-show="isFirstPage">
      <i class="fas fa-angle-double-left"></i>
    </li>

    <li class="c-pagination__item" v-show="!isFirstPage">
      <RouterLink :to="linkToPrev">
        <i class="fas fa-angle-left"></i>
      </RouterLink>
    </li>
    <li class="c-pagination__item is-disabled" v-show="isFirstPage">
      <i class="fas fa-angle-left"></i>
    </li>
    <li
      v-for="page in pageRange"
      :key="page"
      class="c-pagination__item c-pagination__item--page"
      :class="isCurrent(page) ? 'is-active' : ''"
    >
      <RouterLink :to="linkToPage(page)">
        {{ page }}
      </RouterLink>
    </li>
    <li class="c-pagination__item" v-show="!isLastPage">
      <RouterLink :to="linkToNext">
        <i class="fas fa-angle-right"></i>
      </RouterLink>
    </li>
    <li class="c-pagination__item" v-show="!isLastPage">
      <RouterLink :to="linkToLast">
        <i class="fas fa-angle-double-right"></i>
      </RouterLink>
    </li>

    <li class="c-pagination__item is-disabled" v-show="isLastPage">
      <i class="fas fa-angle-right"></i>
    </li>
    <li class="c-pagination__item is-disabled" v-show="isLastPage">
      <i class="fas fa-angle-double-right"></i>
    </li>
  </ul>
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
    // ディレクトリ
    directory: {
      type: String,
      required: true,
    },
    // 検索キーワード
    searchedParam: {
      type: String,
      required: false,
    },
  },
  data() {
    return {
      range: 5, //ページ数を表示する個数
    };
  },
  computed: {
    // 1ページ目へのリンク
    linkToFirst() {
      // 検索キーワードがある場合
      if (this.searchedParam) {
        return `/${this.directory}?page=1&search=${this.searchedParam}`;
      } else {
        // 検索キーワードの指定がない場合
        return `/${this.directory}?page=1`;
      }
    },
    // 最終ページへのリンク
    linkToLast() {
      if (this.searchedParam) {
        return `/${this.directory}?page=${this.lastPage}&search=${this.searchedParam}`;
      } else {
        return `/${this.directory}?page=${this.lastPage}`;
      }
    },
    // 1つ前のページへのリンク
    linkToPrev() {
      if (this.searchedParam) {
        return `/${this.directory}?page=${this.currentPage - 1}&search=${
          this.searchedParam
        }`;
      } else {
        return `/${this.directory}?page=${this.currentPage - 1}`;
      }
    },
    // 次のページへのリンク
    linkToNext() {
      if (this.searchedParam) {
        return `/${this.directory}?page=${this.currentPage + 1}&search=${
          this.searchedParam
        }`;
      } else {
        return `/${this.directory}?page=${this.currentPage + 1}`;
      }
    },

    // 1ページ目かどうか
    isFirstPage() {
      return this.currentPage === 1;
    },
    // 最終ページかどうか
    isLastPage() {
      return this.currentPage === this.lastPage;
    },
    // ページ番号配列の生成
    pageRange() {
      let minPage = "";
      let maxPage = "";
      if (this.currentPage === this.lastPage && this.range < this.lastPage) {
        // 現在のページが総ページ数と同じ かつ 総ページ数が表示項目数以上の場合は
        // 最終ページのリンクとその前のページのリンクを合わせて表示項目数だけリンクを出す
        minPage = this.lastPage - (this.range - 1);
        maxPage = this.lastPage;
      } else if (
        this.currentPage === this.lastPage - 1 &&
        this.range < this.lastPage
      ) {
        // 現在のページが総ページ数の1ページ前 かつ 総ページ数が表示項目数以上の場合は
        // 現在のページ、最終ページのリンクとその前のページのリンクを合わせて表示項目数だけリンクを出す
        minPage = this.currentPage - (this.range - 2);
        maxPage = this.lastPage;
      } else if (this.currentPage === 2 && this.range < this.lastPage) {
        // 現在のページが2 かつ 総ページ数が表示項目数以上の場合は1ページ目から表示項目数だけリンクを出す
        minPage = 1;
        maxPage = 2 + (this.range - 2);
      } else if (this.currentPage === 1 && this.range < this.lastPage) {
        // 現在のページが1 かつ 総ページ数が表示項目数以上の場合は1ページ目から表示項目数だけリンクを出す
        minPage = 1;
        maxPage = 1 + (this.range - 1);
      } else if (this.lastPage <= this.range) {
        // 総ページ数が表示項目数以下の場合は1ページ目から最終ページまでのリンクを出す
        minPage = 1;
        maxPage = this.lastPage;
      } else {
        // その他の場合は左右にリンクを出す
        minPage = this.currentPage - Math.floor(this.range / 2);
        maxPage = this.currentPage + Math.floor(this.range / 2);
      }
      // 配列を生成
      return this.calRange(minPage, maxPage);
    },
  },
  methods: {
    // 引数で指定した開始値から終了値までの配列を生成
    calRange(minPage, maxPage) {
      const range = [];
      for (let i = minPage; i <= maxPage; i++) {
        range.push(i);
      }
      return range;
    },
    // 現在表示しているページかを判別
    isCurrent(page) {
      return page === this.currentPage;
    },
    // ページ番号へのリンク
    linkToPage(page) {
      if (this.searchedParam) {
        return `/${this.directory}?page=${page}&search=${this.searchedParam}`;
      } else {
        return `/${this.directory}?page=${page}`;
      }
    },
  },
};
</script>
