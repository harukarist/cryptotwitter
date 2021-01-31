<template>
  <div>
    <div v-if="isLogin">
      <div class="p-twitter-user">
        <p class="p-twitter-user__title">あなたのTwitterアカウント</p>
        <div class="p-twitter-user__head">
          <img
            :src="usersTwitter.twitter_avatar"
            class="p-twitter-user__avatar"
            :alt="`${usersTwitter.user_name}'s avatar`"
            @error="noImage"
          />
          <div class="p-twitter-user__name">
            <p>{{ usersTwitter.user_name }}</p>
            <p>@{{ usersTwitter.screen_name }}</p>
          </div>
        </div>

        <AutoFollow />

        <a class="p-twitter-user__delete" @click.stop="deleteTwitterAuth()">
          Twitterアカウント連携を解除する
        </a>
      </div>
    </div>
    <div v-if="!isLogin" class="p-twitter__login">
      <div class="p-twitter__welcome">
        <p class="p-twitter__welcome-text">自動フォローをはじめる</p>
      </div>
      <a class="c-btn__twitter" href="/auth/twitter/login">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携
      </a>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート
import AutoFollow from "../components/AutoFollow.vue";

export default {
  name: "TwitterLogin",
  components: {
    AutoFollow,
  },
  props: {
    isLogin: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    ...mapState({
      // twitterストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.twitter.apiStatus,
    }),
    ...mapGetters({
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      usersTwitter: "twitter/usersTwitter",
    }),
  },
  methods: {
    // APIで取得したアバターがリンク切れの場合
    noImage(element) {
      // 代替画像を表示
      element.target.src = "../img/avatar_noimage.png";
    },
    // Twitterアカウント連携を削除
    async deleteTwitterAuth() {
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("twitter/deleteAuth");
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "Twitterアカウントの連携を解除しました",
          type: "success",
          timeout: 2000,
        });
      }
    },
  },
};
</script>
