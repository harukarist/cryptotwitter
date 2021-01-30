<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">関連ニュース一覧</h5>
      <p class="c-section__text">
        仮想通貨に関する最新ニュースをお届けします。<br />
      </p>
      <div class="p-news">
        <div class="p-news__item" v-for="item in news" :key="item.title">
          <div class="p-news__info">
            <span class="p-news__date">
              {{ item.published_date }}
            </span>
            <span class="p-news__source">
              {{ item.source }}
            </span>
          </div>
          <h5 class="p-news__title">
            <a :href="item.url" target="_blank">
              {{ item.title }}
            </a>
          </h5>
        </div>
      </div>

      <Pagination
        :directory="directoryName"
        :current-page="currentPage"
        :last-page="lastPage"
      />
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
import Pagination from "../components/Pagination.vue";

export default {
  components: {
    Pagination,
  },
  data() {
    return {
      news: [], //ニュース一覧を格納する配列を用意
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      directoryName: "news", //ページネーションリンクに付与するディレクトリ
    };
  },
  // ページネーションのページ番号をrouter.jsから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  methods: {
    // axiosでニュース一覧取得APIにリクエスト
    async fetchNews() {
      const response = await axios.get(`/api/news?page=${this.page}`);
      if (response.status !== OK) {
        // 通信失敗の場合
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.news = response.data.data;
      // ページネーションの現在ページ、最終ページの値を格納
      this.currentPage = response.data.current_page;
      this.lastPage = response.data.last_page;
      return;
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        await this.fetchNews();
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
