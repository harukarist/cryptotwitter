<template>
  <div>
    <div class="p-autofollow">
      <div class="p-autofollow__guide">
        <p v-if="totalAutoFollow" class="p-autofollow__total">
          自動フォロー累計数：
          <span class="num">
            {{ totalAutoFollow }}
          </span>
          件
        </p>
        <p
          class="p-autofollow__status c-alert__inline--success"
          v-if="usersTwitter.use_autofollow"
        >
          <i class="fas fa-mug-hot p-autofollow__icon"></i
          >自動フォロー機能を利用中です
        </p>
        <p
          class="p-autofollow__status c-alert__inline--danger"
          v-if="!usersTwitter.use_autofollow"
        >
          <i class="fas fa-ban p-autofollow__icon"></i
          >自動フォロー機能を利用していません
        </p>
      </div>
      <div class="p-autofollow__guide" v-if="usersTwitter.use_autofollow">
        <p class="p-autofollow__text--small">
          15分毎に最大15ユーザー、1日最大1000ユーザーまで<br />毎日ランダムに自動でフォローします。
        </p>
        <a class="c-btn__muted--outline" @click.stop="cancelAutoFollow()">
          自動フォロー機能を解除する
        </a>
      </div>
    </div>

    <div class="p-autofollow" v-if="!usersTwitter.use_autofollow">
      <div class="p-autofollow__guide">
        <p class="p-autofollow__text--small">
          自動フォロー機能を利用すると、<br />
          仮想通貨関連アカウントを<br
            class="u-sp--only"
          />まとめてフォローできます。
        </p>
      </div>
      <button class="c-btn__accent" @click="applyAutoFollow()">
        自動フォロー機能をON
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート
import { OK } from "../utility";

export default {
  name: "AutoFollow",
  data() {
    return {
      totalAutoFollow: 0,
    };
  },
  computed: {
    ...mapState({
      // twitterストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.twitter.apiStatus,
    }),
    ...mapGetters({
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      usersTwitter: "twitter/usersTwitter",
      // // twitterストアのtotalAutoFollowゲッターでユーザーのTwitterアカウント情報を取得
      // totalAutoFollow: "twitter/totalAutoFollow",
    }),
  },
  methods: {
    // 自動フォロー累計数を取得
    async fetchTotalAutoFollow() {
      const response = await axios.get("/api/autofollow/count");
      console.log(response);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // 自動フォロー累計数を格納
      this.totalAutoFollow = response.data;
    },

    // 自動フォロー適用処理
    async applyAutoFollow() {
      // dispatch()でtwitterストアのapplyAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/applyAutoFollow");
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "自動フォローを適用しました",
          type: "success",
          timeout: 6000,
        });
      }
    },
    // 自動フォロー解除処理
    async cancelAutoFollow() {
      // dispatch()でtwitterストアのcancelAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/cancelAutoFollow");
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "自動フォローを解除しました",
          type: "notice",
          timeout: 6000,
        });
      }
    },
  },
  created() {
    // ページ読み込み時に自動フォロー累計数を取得
    this.fetchTotalAutoFollow();
  },
};
</script>
