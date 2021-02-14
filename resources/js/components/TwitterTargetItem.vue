<template>
  <div class="p-target__item">
    <slot name="follow_date"></slot>
    <div class="p-target__head">
      <div class="p-target__head-left">
        <img
          class="p-target__avatar"
          :src="item.profile_img"
          :alt="`${item.user_name}'s avatar`"
          @error="noImage"
        />
        <div class="p-target__name">
          <a :href="`https://twitter.com/${item.screen_name}`" target="_blank">
            {{ item.user_name }}
            <p>@{{ item.screen_name }}</p>
          </a>
        </div>
      </div>

      <div v-if="isTwitterLogin" class="p-target__head-right">
        <button
          v-if="!item.followed_by_user"
          class="c-btn__twitter--outline p-target__btn"
          @click.prevent="follow()"
        >
          フォローする
        </button>
        <button
          v-if="item.followed_by_user"
          class="c-btn--danger--outline p-target__btn"
          @click.prevent="unfollow()"
        >
          フォロー解除
        </button>
      </div>
    </div>
    <div class="p-target__body">
      <p class="p-target__profile">{{ item.profile_text }}</p>

      <div class="p-target__follow">
        <span class="p-target__num">
          {{ item.follow_num }}
        </span>
        <span class="p-target__aside">フォロー中</span>
        <span class="p-target__num">
          {{ item.follower_num }}
        </span>
        <span class="p-target__aside">フォロワー</span>
      </div>
    </div>
    <div class="p-target__tweet">
      <p class="p-target__tweeted-text">{{ item.tweet_text }}</p>
      <p class="p-target__tweeted-at">
        {{ item.tweeted_at }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "TwitterTargetItem",
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    isTwitterLogin() {
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      return this.$store.getters["twitter/check"];
    },
  },
  methods: {
    noImage(element) {
      // APIで取得したアバターがリンク切れの場合は代替画像を表示
      element.target.src = "../img/avatar_noimage.png";
    },
    follow() {
      // $emitでfollowイベントを発火し、親コンポーネントでメソッドを実行
      this.$emit("follow", this.item.twitter_id);
    },
    unfollow() {
      // $emitでunfollowイベントを発火し、親コンポーネントでメソッドを実行
      this.$emit("unfollow", this.item.twitter_id);
    },
  },
};
</script>
