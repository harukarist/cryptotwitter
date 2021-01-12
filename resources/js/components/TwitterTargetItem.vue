<template>
  <div class="p-target__item my-4">
    <ul>
      <img
        class="p-target__avatar"
        :src="item.profile_img"
        :alt="`${item.screen_name}'s avatar`"
      />

      <li>
        <a :href="`https://twitter.com/${item.screen_name}`" target="_blank">
          {{ item.user_name }}</a
        >
      </li>
      <li>@{{ item.screen_name }}</li>
      <li>フォロー数：{{ item.follow_num }}</li>
      <li>フォロワー数：{{ item.follower_num }}</li>
      <li>{{ item.profile_text }}</li>
    </ul>
    <div class="border">
      <ul>
        <li>{{ item.tweet_text }}</li>
        <li>{{ item.tweeted_at }}</li>
      </ul>
    </div>
    <span v-if="!item.followed_by_user">未フォロー</span>
    <span v-if="item.followed_by_user">フォロー済み</span>
    <button
      v-if="!item.followed_by_user"
      class="p-target__action p-target__action--follow"
      @click.prevent="follow()"
    >
      <i class="icon ion-md-heart"></i>フォローする
    </button>
    <button
      v-if="item.followed_by_user"
      class="p-target__action p-target__action--follow"
      @click.prevent="unfollow()"
    >
      <i class="icon ion-md-heart"></i>フォロー解除
    </button>
  </div>
</template>

<script>
import { OK } from "../utility";

export default {
  name: "TwitterTargetItem",
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  methods: {
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
