<template>
  <div>
    <div class="p-autofollow" v-if="usersTwitter.use_autofollow">
      <div class="p-autofollow__guide">
        <p class="p-autofollow__guide-text">
          自動フォロー機能を利用中です。<br />
          15分ごとに最大15ユーザーまでフォローします。
        </p>
        <p v-if="totalAutoFollow" class="p-autofollow__guide-text">
          自動フォロー累計数：
          <span class="num">
            {{ totalAutoFollow }}
          </span>
          件
        </p>
      </div>

      <a class="c-btn__muted-outline" @click.stop="cancelAutoFollow()">
        自動フォロー機能を解除する
      </a>
    </div>

    <div class="p-autofollow" v-if="!usersTwitter.use_autofollow">
      <div class="p-autofollow__guide">
        <p class="p-autofollow__guide-text">
          自動フォロー機能を利用すると、<br />未フォローのユーザーをまとめてフォローできます。
        </p>
        <p v-if="totalAutoFollow" class="p-autofollow__guide-text">
          自動フォロー機能でこれまで{{ totalAutoFollow }}件フォローしました。
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

export default {
  name: "AutoFollow",
  computed: {
    ...mapState({
      // twitterストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.twitter.apiStatus,
    }),
    ...mapGetters({
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      usersTwitter: "twitter/usersTwitter",
      // twitterストアのtotalAutoFollowゲッターでユーザーのTwitterアカウント情報を取得
      totalAutoFollow: "twitter/totalAutoFollow",
    }),
  },
  methods: {
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
};
</script>
