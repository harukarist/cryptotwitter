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
        <pagination-info
          :current-page="currentPage"
          :per-page="perPage"
          :total-num="totalNum"
          :items-length="news.length"
        />
        <search-form-component
          @search="searchNews"
          @clear="clearResult"
          :total-num="totalNum"
          :searched-param="searchedParam"
          item-name="ニュース"
        />

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
            :searched-param="searchedParam"
            :current-page="currentPage"
            :last-page="lastPage"
            :per-page="perPage"
            :total-num="totalNum"
          />
          <pagination-info
            :current-page="currentPage"
            :per-page="perPage"
            :total-num="totalNum"
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
    SearchFormComponent, //キーワード検索フォーム
  },
  // ページネーションのページ番号をrouter.jsから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 1,
    },
  },
  data() {
    return {
      news: [], //ニュース一覧を格納する配列を用意
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      perPage: 0, //1ページあたりの表示件数
      totalNum: 0, //トータル件数
      directoryName: "news", //ページネーションリンクに付与するディレクトリ
      searchedParam: "", //検索したキーワード（ページネーションのクエリパラメータの生成、キーワード検索フォームの検索結果表示に使用）
    };
  },
  methods: {
    // ニュースをキーワード検索
    searchNews(word) {
      // 検索キーワードが空の場合は全てのアカウント一覧を取得
      if (!word) {
        this.clearResult();
        return;
      }
      let params = {
        // 検索コンポーネントから受け取ったキーワードをクエリパラメータに格納
        params: {
          search: word,
        },
      };
      // クエリパラメータを渡してニュース一覧を取得
      this.fetchNews(params);
      this.searchedParam = word;
    },
    // 検索ボックスのテキストを削除
    clearResult() {
      let params = {
        // 検索用のクエリパラメータを指定しない
        params: {
          page: 1,
        },
      };
      this.fetchNews(params);
      // 検索コンポーネントにpropsで渡すsearchedParamを空にする
      this.searchedParam = "";
    },
    // axiosでニュース一覧取得APIにリクエスト
    async fetchNews(params) {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      if (!params) {
        // 入力された検索キーワードがなければクエリパラメータの値を再セット
        params = {
          params: {
            page: this.page,
            search: this.searchedParam,
          },
        };
      }
      // ニュース一覧取得APIへリクエスト
      const response = await axios.get("/api/news", params);

      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
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
      this.totalNum = response.data.total;
      return;
    },
  },
  watch: {
    // ページネーション遷移時にコンポーネントの再生成が必要になるため、
    // $routeを監視し、ページ切り替え時にデータ取得を実行する
    $route: {
      async handler() {
        await this.fetchNews();
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
