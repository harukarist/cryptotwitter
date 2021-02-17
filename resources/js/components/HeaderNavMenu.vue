<template>
  <div
    class="p-nav-menu"
    :class="{ 'is-visible': isActiveDrawerMenu }"
    @click.self="closeMenu"
  >
    <transition name="drawer-menu" appear>
      <div class="p-nav-menu__contents">
        <!-- ドロワーメニュー用ロゴ -->
        <div class="p-nav-menu__sp-title" @click="closeMenu">
          <router-link :to="{ name: 'top' }">
            <img src="/img/logo.png" class="p-navbar__title-logo" />
            <h1 class="p-navbar__title-text">CryptoTrend</h1>
          </router-link>
        </div>

        <!-- ログイン済みユーザー向けメニュー -->
        <div v-if="isLogin" class="p-nav-menu__inner">
          <ul class="p-nav-menu__list">
            <li class="p-nav-menu__item" @click="closeMenu">
              <router-link
                :to="{ name: 'trend.index' }"
                active-class="is-active"
                class="p-nav-menu__link"
              >
                トレンド一覧
              </router-link>
            </li>
            <li class="p-nav-menu__item" @click="closeMenu">
              <router-link
                :to="{ name: 'twitter.index' }"
                active-class="is-active"
                class="p-nav-menu__link"
              >
                Twitterフォロー
              </router-link>
            </li>
            <li class="p-nav-menu__item" @click="closeMenu">
              <router-link
                :to="{ name: 'news.index' }"
                active-class="is-active"
                class="p-nav-menu__link"
              >
                関連ニュース
              </router-link>
            </li>
          </ul>
          <!--  -->
          <div class="p-nav-menu__dropdown" @click.self="clickDropdownHead">
            <div class="p-nav-menu__dropdown-head" @click="clickDropdownHead">
              <img
                :src="usersAvatar"
                class="p-nav-menu__avatar"
                :alt="`${userName}'s avatar`"
                @error="noImage"
              />
              <span class="p-nav-menu__username">{{ userName }}</span>
              <i
                class="fas fa-caret-down p-nav-menu__dropdown-icon"
                v-if="!isActiveDrawerMenu"
              ></i>
            </div>
            <div
              class="p-nav-menu__dropdown-menu"
              v-if="isActiveDropdown || isActiveDrawerMenu"
            >
              <ul class="p-nav-menu__dropdown-list">
                <li class="p-nav-menu__dropdown-item" @click="closeMenu">
                  <router-link
                    :to="{ name: 'edit' }"
                    active-class="is-active"
                    class="p-nav-menu__link p-nav-menu__dropdown-link"
                  >
                    アカウント設定
                  </router-link>
                </li>
                <li class="p-nav-menu__dropdown-item" @click="closeMenu">
                  <a
                    class="p-nav-menu__link p-nav-menu__dropdown-link"
                    @click="logout"
                    >ログアウト</a
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 未ログインユーザー向けメニュー -->
        <div v-if="!isLogin">
          <ul class="p-nav-menu__list">
            <li class="p-nav-menu__item">
              <a href="/#about" class="p-nav-menu__link" @click="closeMenu"
                >CryptoTrendとは？</a
              >
              <!-- <router-link
                :to="{
                  name: 'top',
                  hash: '#about',
                }"
                >CryptoTrendとは？</router-link
              > -->
            </li>
            <li class="p-nav-menu__item">
              <a href="/#reason" class="p-nav-menu__link" @click="closeMenu"
                >選ばれる理由</a
              >
              <!-- <router-link
                :to="{
                  name: 'top',
                  hash: '#reason',
                }"
                >選ばれる理由</router-link
              > -->
            </li>
            <li class="p-nav-menu__item">
              <a href="/#faq" class="p-nav-menu__link" @click="closeMenu"
                >よくあるご質問</a
              >
              <!-- <router-link to="/#faq">
                よくあるご質問
              </router-link> -->
            </li>
            <li class="p-nav-menu__item-btn" @click="closeMenu">
              <router-link
                :to="{ name: 'register' }"
                class="c-btn--accent p-nav-menu__btn"
              >
                ユーザー登録
              </router-link>
            </li>
            <li class="p-nav-menu__item-btn" @click="closeMenu">
              <router-link
                :to="{ name: 'login' }"
                class="c-btn--main-outline p-nav-menu__btn"
              >
                ログイン
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート

export default {
  props: {
    isActiveDrawerMenu: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      isActiveDropdown: false,
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
    }),
    ...mapGetters({
      // authストアのcheckゲッターでユーザーのログイン状態をチェック
      isLogin: "auth/check",
      // authストアのuserNameゲッターでユーザー名を取得
      userName: "auth/userName",
      // authストアのuserNameゲッターでユーザー名を取得
      usersAvatar: "twitter/usersAvatar",
    }),
  },
  methods: {
    // APIで取得したアバターがリンク切れの場合
    noImage(element) {
      // 代替画像を表示
      element.target.src = "/img/avatar_noimage.png";
    },
    async logout() {
      // dispatch()でauthストアのlogoutアクションを呼び出す
      await this.$store.dispatch("auth/logout");

      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch(
          "message/showMessage",
          {
            text: "ログアウトしました",
            type: "success",
            timeout: 3000,
          },
          { root: true }
        );
        this.closeMenu(); //ドロワーメニュー、ドロップダウンメニューが開いていたら閉じる

        // VueRouterのpush()でトップ画面に遷移
        this.$router.push({ name: "top" });
      }
    },
    // スマホ用ドロワーメニューを閉じる
    closeMenu() {
      // ドロワーメニュー表示中にクリックされた場合はドロワーメニューを閉じる
      if (this.isActiveDrawerMenu) {
        // 親コンポーネントに通知して親コンポーネント側のdataを変更する
        this.$emit("close");
      }
      // ドロップダウンメニューが開いている場合はドロップダウンメニューを閉じる
      if (this.isActiveDropdown) {
        this.isActiveDropdown = false;
      }
    },
    // ドロップダウンメニューの初期表示部分をクリックした時
    clickDropdownHead() {
      // ドロワーメニュー表示中にクリックされた場合はアカウント設定画面へ遷移して
      // ドロワーメニューを閉じる
      if (this.isActiveDrawerMenu) {
        this.$router.push({ name: "edit" });
        this.$emit("close");
      }
      // その他の場合はドロップダウンメニューの開閉を切り替える
      this.isActiveDropdown = !this.isActiveDropdown;
    },
  },
  created() {
    // ページ読み込み時にフラグを初期化
    this.isActiveDropdown = false;
  },
};
</script>
