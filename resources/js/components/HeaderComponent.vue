<template>
  <nav class="p-navbar">
    <!-- サイトロゴ -->
    <div class="p-navbar__title">
      <RouterLink :to="{ name: 'top' }">
        <img
          src="/img/logo.png"
          class="p-navbar__title-logo">
        <h1 class="p-navbar__title-text">
          CryptoTrend
        </h1>
      </RouterLink>
    </div>

    <!-- スマホ用アクションメニュー -->
    <div class="p-navbar__sp">
      <!-- 未ログイン時のアクションボタン -->
      <ul
        v-if="!isLogin"
        class="p-nav-menu__action">
        <li
          class="p-nav-menu__action-item-btn"
          @click="closeDrawerMenu">
          <RouterLink
            :to="{ name: 'register' }"
            class="c-btn--accent p-nav-menu__action-btn">
            ユーザー登録
          </RouterLink>
        </li>
        <li
          class="p-nav-menu__action-item-btn"
          @click="closeDrawerMenu">
          <RouterLink
            :to="{ name: 'login' }"
            class="c-btn--main-outline p-nav-menu__action-btn">
            ログイン
          </RouterLink>
        </li>
      </ul>
      <!-- ハンバーガーアイコン -->
      <div
        ref="toggleIcon"
        class="p-navbar__toggle"
        :class="{ 'is-active': isActiveDrawerMenu }"
        @click="toggleDrawerMenu">
        <span class="p-navbar__toggle--line" />
        <span class="p-navbar__toggle--line" />
        <span class="p-navbar__toggle--line" />
        <span class="p-navbar__toggle--text" />
      </div>
    </div>

    <!-- ナビメニュー  -->
    <header-nav-menu
      :is-active-drawer-menu="isActiveDrawerMenu"
      @close="closeDrawerMenu" />
  </nav>
</template>

<script>
import { mapState, mapGetters } from 'vuex' // VuexのmapState関数,mapGetters関数をインポート
import HeaderNavMenu from './HeaderNavMenu.vue'

export default {
	components: {
		HeaderNavMenu,
	},
	data() {
		return {
			isActiveDrawerMenu: false,
		}
	},
	computed: {
		...mapState({
			// authストアのステートを参照し、API通信の成否ステータスを取得
			apiStatus: (state) => state.auth.apiStatus,
		}),
		...mapGetters({
			// authストアのcheckゲッターでユーザーのログイン状態をチェック
			isLogin: 'auth/check',
		}),
	},
	created() {
		// ページ読み込み時にフラグを初期化
		this.closeDrawerMenu()
	},
	methods: {
		toggleDrawerMenu() {
			this.isActiveDrawerMenu = !this.isActiveDrawerMenu
		},
		closeDrawerMenu() {
			// ドロワーメニューを閉じる
			this.isActiveDrawerMenu = false
		},
	},
}
</script>
