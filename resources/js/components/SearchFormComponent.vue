<template>
  <form class="c-search__form u-mb--l" @submit.prevent="search">
    <span class="c-search__icon"><i class="fas fa-search"></i></span>
    <input
      type="search"
      class="c-input c-search__input"
      name="search"
      v-model="searchWord"
      placeholder="ニュースをキーワード検索"
      @keydown.enter.prevent="search"
    />
    <span class="c-search__clear-icon">
      <i v-show="this.searchWord" class="fas fa-times" @click="clearWord"></i>
    </span>
  </form>
</template>

<script>
export default {
  data() {
    return {
      searchWord: "", // 検索キーワード（v-modelでフォームの入力値と紐付け）
    };
  },
  methods: {
    search() {
      this.$emit("search", this.searchWord);
    },
    clearWord() {
      this.searchWord = "";
      this.$emit("clear");
    },
  },
};
</script>
