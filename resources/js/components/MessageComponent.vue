<template>
  <transition name="slide-left">
    <div
      v-if="flashMessage && isShow"
      class="c-alert p-message-large"
      :class="`c-alert--${messageType} p-message-large--${messageType}`">
      <p
        class="p-message-large__text"
        :class="`p-message-large__text--${messageType}`">
        {{ flashMessage }}
      </p>
      <i
        class="fas fa-times p-message-large__close"
        @click="closeMessage()" />
    </div>
  </transition>
</template>

<script>
export default {
	props: {
		message: {
			type: String,
			required: true,
		},
		type: {
			type: String,
			required: true,
			default: 'success',
		},
		timeout: {
			type: Number,
			required: true,
			default: 3000,
		},
	},
	data() {
		return {
			isShow: true,
			timeoutId: -1,
		}
	},
	computed: {
		// フラッシュメッセージを表示
		flashMessage() {
			return this.message
		},
		// CSSクラスを返却
		messageType() {
			return this.type
		},
	},

	mounted() {
		// DOMが作成されたらフラッシュメッセージのタイムアウトを設定
		this.timeoutMessage()
	},

	methods: {
		timeoutMessage() {
			// timeoutIdが初期値以外の場合は1つ前のタイムアウトが実行中のため、実行中のtimeoutIdをclearTimeout()に渡して停止する
			if (this.timeoutId !== -1) {
				clearTimeout(this.timeoutId)
				// timeoutIdのステートを初期化
				this.clearTimeoutId()
			}
			// 3秒が経過したらメッセージを閉じるタイムアウトを設定
			const timeoutId = setTimeout(() => {
				this.closeMessage()
			}, this.timeout)
			// setTimeout()の戻り値timeoutIdをにセット
			this.setTimeoutId(timeoutId)
		},
		// フラッシュメッセージを閉じる
		closeMessage() {
			this.isShow = false
		},
		// タイムアウトを管理するtimeoutIdをセット
		setTimeoutId(value) {
			this.timeoutId = value
		},
		// timeoutIdをクリア
		clearTimeoutId() {
			this.timeoutId = -1
		},
	},
}
</script>
