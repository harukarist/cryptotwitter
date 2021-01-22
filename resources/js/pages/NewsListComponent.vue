<template>
  <div class="container c-container">
    <div class="row justify-content-center">
      <h5 class="c-container__title">関連ニュース一覧</h5>
    </div>
    <Pagination
      :directory="directoryName"
      :current-page="currentPage"
      :last-page="lastPage"
    />
    <div class="p-news" v-for="item in news" :key="item.title">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <a :href="item.url">
              {{ item.title }}
            </a>
          </h5>
          <p class="card-text">{{ item.published_date }} - {{ item.source }}</p>
        </div>
      </div>
    </div>
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
      directoryName: "news",
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
      const response = await axios.get(`api/news?page=${this.page}`);
      // console.log(response.data);
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
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
