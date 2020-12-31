import Vue from 'vue'
import Vuex from 'vuex'
import auth from './auth' // ストアのauthモジュール
import error from './error' // ストアのerrorモジュール

// Vuexを使用して、どのコンポーネントからでもデータを参照・更新できるようにする
Vue.use(Vuex)

// ストアを作成
const store = new Vuex.Store({
  modules: {
    auth,
    error
  }
})

export default store
