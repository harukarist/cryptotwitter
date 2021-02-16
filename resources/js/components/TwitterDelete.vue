<template>
  <div class="c-fade-in--small">
    <div v-if="isTwitterLogin">
      <div class="p-twitter-user">
        <p class="p-twitter-user__head">
          {{ userName }}さんのTwitterアカウント
        </p>
        <div class="p-twitter-user__contents">
          <img
            :src="usersTwitter.twitter_avatar"
            class="p-twitter-user__avatar"
            :alt="`${usersTwitter.user_name}'s avatar`"
            @error="noImage"
          />
          <div class="p-twitter-user__account">
            <a
              :href="`https://twitter.com/${usersTwitter.screen_name}`"
              target="_blank"
            >
              <p class="p-twitter-user__name">{{ usersTwitter.user_name }}</p>
              <p class="p-twitter-user__screen">
                @{{ usersTwitter.screen_name }}
              </p>
            </a>
          </div>
        </div>

        <AutoFollowApply />
      </div>

      <div class="p-twitter-user__delete">
        <a class="p-twitter-user__delete-link" @click="openModal">
          Twitterアカウント連携を解除する
        </a>

        <!-- コンポーネント ModalComponent -->
        <ModalComponent @close="closeModal" v-if="modal">
          <!-- default スロットコンテンツ -->
          <p>Vue.js Modal Window!</p>
          <div><input v-model="message" /></div>
          <!-- /default -->
          <!-- footer スロットコンテンツ -->
          <template slot="footer">
            <button @click="doSend">送信</button>
          </template>
          <!-- /footer -->
        </ModalComponent>

        <div v-if="showConfirm" class="p-twitter-user__confirm">
          <i
            class="fas fa-times p-twitter-user__confirm-close"
            @click="showConfirm = false"
          ></i>
          <p class="p-twitter-user__confirm-text">
            Twitterアカウント連携を解除すると<br />
            これまでの自動フォロー履歴も<br
              class="u-sp--only"
            />削除されます。<br />
            本当に連携を解除しますか？<br />
          </p>
          <a class="c-btn--danger" @click.stop="deleteTwitterAuth()">
            Twitterアカウント連携を解除
          </a>
        </div>
      </div>
    </div>
    <div v-if="!isTwitterLogin" class="p-twitter__login">
      <p class="c-catch u-mb--l">
        Twitterアカウントを登録して<br />自動フォローを始める
      </p>
      <a class="c-btn--twitter" href="/auth/twitter/login">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携
      </a>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート
import AutoFollowApply from "../components/AutoFollowApply.vue";

export default {
  name: "TwitterLogin",
  components: {
    AutoFollowApply,
  },
  data() {
    return {
      showConfirm: false,
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
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      isTwitterLogin: "twitter/check",
      // authストアのuserNameゲッターでユーザー名を取得
      userName: "auth/userName",
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
