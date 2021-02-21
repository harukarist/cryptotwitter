<template>
  <ul v-if="messages">
    <li
      v-for="message in messages"
      :key="message"
      class="c-valid__error">
      {{ message }}
    </li>
  </ul>
</template>

<script>
export default {
	props: {
		messages: {
			type: Array,
			required: true,
		},
	},
}
</script>

