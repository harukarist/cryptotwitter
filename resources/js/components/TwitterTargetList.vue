<template>
  <div class="p-target">
    <div class="p-target__list">
      <search-form-component
        @search="searchTargets"
        @clear="clearResult"
        class="u-mt--xl u-mb--xxxl"
      />

      <h5 class="p-target__title">仮想通貨関連アカウント</h5>
      <twitter-target-item
        v-for="target in targets"
        :key="target.id"
        :item="target"
        @follow="createFollow"
        @unfollow="destroyFollow"
      />
      <p v-if="totalItems === 0" class="u-font--center">
        アカウントが存在しません
      </p>

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
          :items-length="targets.length"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { OK } from "../utility";
import TwitterTargetItem from "../components/TwitterTargetItem.vue";
import PaginationLink from "../components/PaginationLink.vue";
import PaginationInfo from "../components/PaginationInfo.vue";
import SearchFormComponent from "../components/SearchFormComponent.vue";

export default {
  components: {
    TwitterTargetItem,
    PaginationLink, //ページ番号付きページネーション（PC、タブレットで表示）
    PaginationInfo, //ページネーションの件数表示
    SearchFormComponent,
  },
  // ページネーションのページ番号を親コンポーネントTwitterListComponentから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      targets: [], //仮想通貨関連アカウント一覧を格納する配列を用意
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      perPage: 0, //1ページあたりの表示件数
      totalItems: 0, //トータル件数
      directoryName: "twitter", //ページネーションリンクに付与するディレクトリ
    };
  },
  methods: {
    // 仮想通貨アカウントを検索
    searchTargets(word) {
      let params = {
        // 検索コンポーネントから受け取ったキーワードをクエリパラメータに格納
        params: {
          search: word,
        },
      };
      // クエリパラメータを渡してTwitterアカウント一覧を取得
      this.fetchTargets(params);
    },
    // 検索結果の表示を解除
    clearResult() {
      // 検索用のクエリパラメータを指定しない
      let params = {};
      this.fetchTargets(params);
    },
    // Twitterアカウント一覧を取得
    async fetchTargets(params) {
      const response = await axios.get(
        `/api/twitter?page=${this.page}`,
        params
      );
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
      this.totalItems = response.data.total;
      return;
    },
    // フォロー登録メソッド
    async createFollow(id) {
      const response = await axios.post(`/api/twitter/${id}/follow`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをtrueにしてフォローボタンの表示を変更
          target.followed_by_user = true;
        }
        return target;
      });
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "フォローしました",
        type: "success",
        timeout: 2000,
      });
    },
    // フォロー解除メソッド
    async destroyFollow(id) {
      const response = await axios.post(`/api/twitter/${id}/unfollow`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをfalseにしてフォローボタンの表示を変更
          target.followed_by_user = false;
        }
        return target;
      });
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "フォロー解除しました",
        type: "success",
        timeout: 2000,
      });
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
        await this.fetchTargets();
        this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
