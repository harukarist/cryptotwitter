const state = {
  loading: false
}

const mutations = {
  start(state) {
    state.loading = true
    // console.log('ローディング中')
  },
  end(state) {
    state.loading = false
    // console.log('ローディング終了')
  }
}

export default {
  namespaced: true,
  state,
  mutations
}
