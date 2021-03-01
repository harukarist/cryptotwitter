<template>
  <div class="p-target">
    <div class="p-target__list">
      <h5 class="p-target__title">
        仮想通貨関連アカウント
      </h5>
      <pagination-info
        :current-page="currentPage"
        :per-page="perPage"
        :total-num="totalNum"
        :items-length="targets.length"
      />
      <search-form-component
        :total-num="totalNum"
        :searched-param="searchedParam"
        item-name="仮想通貨関連アカウント"
        @search="searchTargets"
        @clear="clearResult"
      />

      <twitter-target-item
        v-for="target in targets"
        :key="target.id"
        :item="target"
        @follow="createFollow"
        @unfollow="destroyFollow"
      />
      <div
        v-if="!searchedParam && totalNum === 0"
        class="u-font--center"
      >
        <p v-if="useAutoFollow">
          アカウントはすべて自動フォロー済みです
        </p>
        <p v-if="!useAutoFollow">
          仮想通貨関連アカウントが取得できませんでした
        </p>
      </div>

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
          :items-length="targets.length"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { OK } from "../utility";
import { mapGetters } from "vuex"; // VuexのmapGetters関数をインポート
import TwitterTargetItem from "../components/TwitterTargetItem.vue";
import PaginationLink from "../components/PaginationLink.vue";
import PaginationInfo from "../components/PaginationInfo.vue";
import SearchFormComponent from "../components/SearchFormComponent.vue";

export default {
  components: {
    TwitterTargetItem,
    PaginationLink, //ページ番号付きページネーション（PC、タブレットで表示）
    PaginationInfo, //ページネーションの件数表示
    SearchFormComponent, //キーワード検索フォーム
  },
  // ページネーションのページ番号を親コンポーネントTwitterListComponentから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 1,
    },
  },
  data() {
    return {
      targets: [], //仮想通貨関連アカウント一覧を格納する配列を用意
      currentPage: 1, //現在ページ
      lastPage: 1, //最終ページ
      perPage: 10, //1ページあたりの表示件数
      totalNum: 0, //トータル件数
      directoryName: "twitter", //ページネーションリンクに付与するディレクトリ
      searchedParam: "", //検索したキーワード（ページネーション のクエリパラメータの生成、キーワード検索フォームの検索結果表示に使用）
    };
  },
  computed: {
    ...mapGetters({
      // twitterストアのuseAutoFollowゲッターでユーザーの自動フォロー利用有無を取得
      useAutoFollow: "twitter/useAutoFollow",
    }),
  },
  watch: {
    // ページネーション遷移時にコンポーネントの再生成が必要になるため、
    // $routeを監視し、ページ切り替え時にデータ取得を実行する
    $route: {
      async handler() {
        await this.fetchTargets();
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
  methods: {
    // 検索コンポーネントからキーワードを受け通り、仮想通貨アカウントを検索
    searchTargets(word) {
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
      // クエリパラメータを渡してTwitterアカウント一覧を取得
      this.fetchTargets(params);
      this.searchedParam = word;
    },
    // 検索結果の表示を解除
    clearResult() {
      // 検索用のクエリパラメータを指定しない
      let params = {
        params: {
          page: 1,
        },
      };
      this.fetchTargets(params);
      this.searchedParam = "";
    },
    // Twitterアカウント一覧を取得
    async fetchTargets(params) {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      if (!params) {
        params = {
          params: {
            page: this.page,
            search: this.searchedParam,
          },
        };
      }
      // 仮想通貨アカウントの一覧取得APIへリクエスト
      const response = await axios.get("/api/twitter", params);

      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.targets = response.data.data;

      // ページネーションの現在ページ、最終ページの値を格納
      this.currentPage = response.data.current_page;
      this.lastPage = response.data.last_page;
      // 1ページあたりの表示件数、トータル件数を格納
      this.perPage = response.data.per_page;
      this.totalNum = response.data.total;
      return;
    },
    // 仮想通貨アカウントを1件フォローするメソッド
    async createFollow(id) {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      const response = await axios.post(`/api/twitter/${id}/follow`);
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }

      // レスポンスがOKで、Laravel側で指定したフォロー完了フラグ'is_done'がtrueの場合
      if (response.data.is_done) {
        // 仮想通貨アカウント一覧オブジェクトのうち、フォロー対象アカウントの表示を変更
        this.targets = this.targets.map((target) => {
          if (target.twitter_id === response.data.target_id) {
            // フォロー済みフラグをtrueにしてフォローボタンの表示を変更
            target.followed_by_user = true;
          }
          return target;
        });
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "アカウントをフォローしました",
          type: "success",
          timeout: 2000,
        });
      } else {
        // フォロー完了フラグ'is_done'がfalseの場合はサーバーから返却されたエラーメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: response.data.message,
          type: "danger",
          timeout: 2000,
        });
      }
    },
    // 仮想通貨アカウントを1件フォロー解除するメソッド
    async destroyFollow(id) {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      const response = await axios.post(`/api/twitter/${id}/unfollow`);
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }

      // レスポンスがOKで、Laravel側で指定したフォロー解除完了フラグ'is_done'がtrueの場合
      if (response.data.is_done) {
        // 仮想通貨アカウント一覧オブジェクトのうち、フォロー解除対象アカウントの表示を変更
        this.targets = this.targets.map((target) => {
          if (target.twitter_id === response.data.target_id) {
            // フォロー済みフラグをfalseにしてフォローボタンの表示を変更
            target.followed_by_user = false;
          }
          return target;
        });
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "フォローを解除しました",
          type: "success",
          timeout: 2000,
        });
      } else {
        // フォロー解除完了フラグ'is_done'がfalseの場合はサーバーから返却されたエラーメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: response.data.message,
          type: "danger",
          timeout: 2000,
        });
      }
    },
  },
};
</script>
