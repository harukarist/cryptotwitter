const state = {
  isLoading: false,
}

const mutations = {
  setIsLoading(state, status) {
    state.isLoading = status
    console.log(status);
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
