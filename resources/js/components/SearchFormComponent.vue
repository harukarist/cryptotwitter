<template>
  <div class="c-search">
    <form class="c-search__form" @submit.prevent="search">
      <span class="c-search__icon"><i class="fas fa-search"></i></span>
      <input
        type="text"
        class="c-input c-search__input"
        name="search"
        v-model="searchWord"
        placeholder="キーワードを入力して検索"
      />
      <span class="c-search__clear-icon">
        <i v-show="searchWord" class="fas fa-times" @click="clearWord"></i>
      </span>
    </form>
    <div v-if="searchedParam && totalNum === 0" class="c-search__notfound">
      <p class="u-font--center">
        「{{ searchedParam }}」を含む{{ itemName }}が見つかりませんでした
      </p>
    </div>
    <div v-if="searchedParam && totalNum > 0" class="c-search__found u-mb--xl">
      <p class="u-font--center">
        「{{ searchedParam }}」を含む{{ itemName }}が<br class="u-sp--only" />
        {{ totalNum }}件見つかりました
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    totalNum: {
      type: Number,
      required: true,
    },
    searchedParam: {
      type: String,
      required: true,
    },
    itemName: {
      type: String,
      required: true,
    },
  },
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
