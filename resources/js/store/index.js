import Vue from 'vue'
import Vuex from 'vuex'

// ストアのモジュールをインポート
import auth from './auth'
import error from './error'

// Vuexを使用して、どのコンポーネントからでもデータを参照・更新できるようにする
Vue.use(Vuex)

// ストアを作成
const store = new Vuex.Store({
  // ストアのモジュールを登録
  modules: {
    auth,
    error,
  }
})

export default store
