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
              自動フォロー機能でこれまで{{
                totalAutoFollow
              }}件フォローしました。
            </p>
          </div>
          <button class="c-btn__main" @click="applyAutoFollow()">
            自動フォロー機能を利用する
          </button>
        </div>
        <a class="p-twitter-user__delete" href="./auth/twitter/delete">
          Twitterアカウント連携を解除する
        </a>
      </div>

      <!-- データ全体の状態を確認 -->
      <!-- <pre>{{ $data }}</pre> -->
    </div>
    <div v-if="!isLogin" class="p-twitter__login">
      <a class="c-btn__twitter" href="./auth/twitter/login">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: "TwitterLogin",
  props: {
    isLogin: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    usersTwitter() {
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      return this.$store.getters["twitter/usersTwitter"];
    },
    totalAutoFollow() {
      // twitterストアのtotalAutoFollowゲッターでユーザーのTwitterアカウント情報を取得
      return this.$store.getters["twitter/totalAutoFollow"];
    },
  },
  methods: {
    noImage(element) {
      // APIで取得したアバターがリンク切れの場合は代替画像を表示
      element.target.src = "../img/avatar_noimage.png";
    },
    async applyAutoFollow() {
      // dispatch()でtwitterストアのapplyAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/applyAutoFollow");
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "自動フォローを適用しました",
        type: "success",
        timeout: 6000,
      });
    },
    async cancelAutoFollow() {
      // dispatch()でtwitterストアのcancelAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/cancelAutoFollow");
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "自動フォローを解除しました",
        type: "notice",
        timeout: 6000,
      });
    },
  },
};
</script>