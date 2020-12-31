import Vue from 'vue'
import Vuex from 'vuex'

import auth from './auth'

Vue.use(Vuex)

// ストアを作成
const store = new Vuex.Store({
  modules: {
    auth //importしたauth.jsをモジュールとして登録
  }
})

export default store
