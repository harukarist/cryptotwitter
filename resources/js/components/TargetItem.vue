<template>
  <div class="p-target__item my-4">
    <ul>
      <img
        class="p-target__avatar"
        :src="item.profile_img"
        :alt="`${item.screen_name}'s avatar`"
      />
      <li>{{ item.user_name }}</li>
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
    <button class="p-target__action p-target__action--follow" title="Follow">
      <i class="icon ion-md-heart"></i>Follow
    </button>
    <a
      class="p-target__action p-target__action--follow"
      title="Follow"
      @click.stop
      :href="`/twitter/${item.twitter_id}/follow`"
    >
      <i class="icon ion-md-arrow-round-down"></i>Follow
    </a>
  </div>
</template>

<script>
export default {
  name: "TargetItem",
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
};
</script>
