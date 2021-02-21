<template>
  <div>
    <div class="p-autofollow">
      <div class="p-autofollow__guide">
        <p
          v-if="useAutoFollow"
          class="p-autofollow__status c-alert__inline--success">
          <i class="fas fa-mug-hot p-autofollow__icon" />自動フォロー機能を利用中です
        </p>
        <p
          v-if="!useAutoFollow"
          class="p-autofollow__status c-alert__inline--danger">
          <i class="fas fa-ban p-autofollow__icon" />自動フォロー機能を<br
            class="u-sp--only">利用していません
        </p>
      </div>
      <div
        v-if="useAutoFollow"
        class="p-autofollow__guide">
        <p class="p-autofollow__text--small">
          15分毎に最大15ユーザーまで<br>毎日ランダムに自動でフォローします。
        </p>
        <a
          class="c-btn--muted-outline c-btn--small"
          @click.stop="cancelAutoFollow()">
          自動フォロー機能を解除する
        </a>
      </div>

      <div
        v-if="!useAutoFollow"
        class="p-autofollow__guide">
        <p class="c-catch u-mb--l p-autofollow__text--small">
          自動フォロー機能を利用すると<br>
          仮想通貨関連アカウントを<br
            class="u-sp--only">まとめてフォローできます
        </p>
        <button
          class="c-btn--accent c-btn--large"
          @click="applyAutoFollow()">
          自動フォロー機能をON
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex' // VuexのmapState関数,mapGetters関数をインポート

export default {
	computed: {
		...mapState({
			// twitterストアのステートを参照し、API通信の成否ステータスを取得
			apiStatus: (state) => state.twitter.apiStatus,
		}),
		...mapGetters({
			// twitterストアのuseAutoFollowゲッターでユーザーの自動フォロー利用有無を取得
			useAutoFollow: 'twitter/useAutoFollow',
		}),
	},
	methods: {
		// 自動フォロー適用処理
		async applyAutoFollow() {
			// dispatch()でtwitterストアのapplyAutoFollowアクションを呼び出す
			await this.$store.dispatch('twitter/applyAutoFollow')
			// API通信が成功した場合
			if (this.apiStatus) {
				// フラッシュメッセージを表示
				this.$store.dispatch('message/showMessage', {
					text: '自動フォローを適用しました',
					type: 'success',
					timeout: 2000,
				})
			}
		},
		// 自動フォロー解除処理
		async cancelAutoFollow() {
			// dispatch()でtwitterストアのcancelAutoFollowアクションを呼び出す
			await this.$store.dispatch('twitter/cancelAutoFollow')
			// API通信が成功した場合
			if (this.apiStatus) {
				// フラッシュメッセージを表示
				this.$store.dispatch('message/showMessage', {
					text: '自動フォローを解除しました',
					type: 'notice',
					timeout: 2000,
				})
			}
		},
	},
}
</script>
