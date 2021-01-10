import Vue from 'vue'
import Vuex from 'vuex'

// ストアのモジュールをインポート
import auth from './auth'
import twitter from './twitter'
import error from './error'

// Vuexを使用して、どのコンポーネントからでもデータを参照・更新できるようにする
Vue.use(Vuex)

// ストアを作成
const store = new Vuex.Store({
  // 商用環境以外はStrictモードをtrueにして警告を表示
  strict: process.env.NODE_ENV !== 'production',

  // ストアのモジュールを登録
  modules: {
    auth,
    twitter,
    error,
  }
})

export default store
