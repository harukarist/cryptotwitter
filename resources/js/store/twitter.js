import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../utility'


// データを格納するステート
const state = {
  usersTwitter: null, //ログイン済みユーザーの情報を保持
}

// ステートの値から状態を算出するゲッター
const getters = {
  // ユーザーが認証済みかどうかをチェックする
  check: state => !!state.usersTwitter, //ログインチェック（二重否定で確実に真偽値を返す）
  usersTwitter: state => state.usersTwitter ? state.usersTwitter : '', //ユーザー名を格納（nullの場合は空文字を返す）
  // screenname: state => state.usersTwitter ? state.usersTwitter.screen_name : '', //ユーザー名を格納（nullの場合は空文字を返す）
  // avatar: state => state.usersTwitter ? state.usersTwitter.twitter_avatar : '', //ユーザー名を格納（nullの場合は空文字を返す）
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
  // userステートの値を更新する処理
  setUsersTwitter(state, usersTwitter) {
    state.usersTwitter = usersTwitter
  },
}

// 非同期処理を行い、ミューテーションにcommitするアクション
// アクションの第一引数に、commit()などを持つコンテキストオブジェクトを渡す
const actions = {

  // ログイン処理
  async checkAuth(context) {
    // サーバーのAPIを呼び出し
    const response = await axios.get('/api/auth/twitter/check')
    // 返却されたユーザー情報（未ログインの場合はnull）を格納
    const usersTwitter = response.data || null
    // API通信が成功した場合
    if (response.status === OK) {
      // setUserミューテーションでuserステートを更新
      context.commit('setUsersTwitter', usersTwitter)
      return false
    }
  },
}


// ストアオブジェクトとしてエクスポート
export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
