<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">
      アカウントの削除
    </h2>
    <div class="c-form__wrapper">
      <form
        class="c-form--large"
        @submit.prevent="withdraw"
      >
        <p class="c-section__text">
          アカウントを削除すると、<br>
          これまでのデータは全て削除されます。<br>
          本当に削除しますか？<br>
        </p>
        <div class="c-form__button">
          <button
            type="submit"
            class="c-btn--danger"
          >
            アカウントを削除する
          </button>
        </div>
      </form>
      <div class="c-form__link">
        <router-link
          :to="{ name: 'edit' }"
          class="c-form__link"
        >
          アカウント設定に戻る
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex"; // VuexのmapState関数をインポート

export default {
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
    }),
  },
  methods: {
    // 退会WebAPI呼び出し
    async withdraw() {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("auth/withdraw");
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "退会手続きが完了しました",
          type: "success",
          timeout: 2000,
        });
        // VueRouterのpush()でトップ画面に遷移
        this.$router.push({ name: "top" });
      }
    },
  },
};
</script>
