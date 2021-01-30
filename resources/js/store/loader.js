export default {
  namespaced: true,
  state: {
    loading: false
  },
  // router.jsでルーターナビゲーションの前後に設定
  mutations: {
    start(state) {
      state.loading = true
      console.log('ローディング中')
    },
    end(state) {
      state.loading = false
      console.log('ローディング終了')
    }
  }
}
