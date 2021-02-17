<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">関連ニュース一覧</h5>
      <p class="c-section__text">
        仮想通貨に関する最新ニュースを<br
          class="u-sp--only"
        />お届けします。<br />
      </p>
      <div class="p-news c-fade-in">
        <search-form-component @search="searchNews" @clear="clearResult" />
        <p v-if="totalItems === 0" class="u-font--center">
          ニュースが存在しません
        </p>

        <transition-group tag="div" name="popup" appear class="p-news__item">
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
        </transition-group>

        <div class="c-pagination">
          <pagination-link
            :directory="directoryName"
            :current-page="currentPage"
            :last-page="lastPage"
            :per-page="perPage"
            :total-items="totalItems"
          />
          <pagination-info
            :current-page="currentPage"
            :per-page="perPage"
            :total-items="totalItems"
            :items-length="news.length"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
import PaginationLink from "../components/PaginationLink.vue";
import PaginationInfo from "../components/PaginationInfo.vue";
import SearchFormComponent from "../components/SearchFormComponent.vue";

export default {
  components: {
    PaginationLink, //ページ番号付きページネーション（PC、タブレットで表示）
    PaginationInfo, //ページネーションの件数表示
    SearchFormComponent,
  },
  // ページネーションのページ番号をrouter.jsから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      news: [], //ニュース一覧を格納する配列を用意
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      perPage: 0, //1ページあたりの表示件数
      totalItems: 0, //トータル件数
      directoryName: "news", //ページネーションリンクに付与するディレクトリ
    };
  },
  methods: {
    // ニュースをキーワード検索
    searchNews(word) {
      let params = {
        // 検索コンポーネントから受け取ったキーワードをクエリパラメータに格納
        params: {
          search: word,
        },
      };
      // クエリパラメータを渡してニュース一覧を取得
      this.fetchNews(params);
    },
    // 検索結果の表示を解除
    clearResult() {
      // 検索用のクエリパラメータを指定しない
      let params = {};
      this.fetchNews(params);
    },
    // axiosでニュース一覧取得APIにリクエスト
    async fetchNews(params) {
      // this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      const response = await axios.get(`/api/news?page=${this.page}`);
      // const response = await axios.get(`/api/news?page=${this.page}`, params);
      // this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
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
      // 1ページあたりの表示件数、トータル件数を格納
      this.perPage = response.data.per_page;
      this.totalItems = response.data.total;
      return;
    },
  },
  // created() {
  //   // ページ読み込み時にニュースを取得
  //   this.fetchNews();
  // },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
        await this.fetchNews();
        this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
