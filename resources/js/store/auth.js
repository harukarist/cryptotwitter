import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../utility'

// ユーザーの認証状態を管理するauthモジュール
// ページを遷移してもログイン状態を保持するため、Vuexで状態管理を行う

// データを格納するステート
const state = {
  user: null, //ログイン済みユーザーの情報を保持
  apiStatus: null, //API呼び出しの成否を格納
  registerErrorMessages: null, //ユーザー登録エラーメッセージを格納
  loginErrorMessages: null, //ログインエラーメッセージを格納
}

// ステートの値から状態を算出するゲッター
const getters = {
  // ユーザーが認証済みかどうかをチェックする
  check: state => !!state.user, //ログインチェック（二重否定で確実に真偽値を返す）
  username: state => state.user ? state.user.name : '' //ユーザー名を格納（nullの場合は空文字を返す）
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
  // userステートの値を更新する処理
  setUser(state, user) {
    state.user = user
  },
  // apiStatusステートを更新する処理
  setApiStatus(state, status) {
    state.apiStatus = status
  },
  // registerErrorMessagesステートを更新する処理
  setRegisterErrorMessages(state, messages) {
    state.registerErrorMessages = messages
  },
  // loginErrorMessagesステートを更新する処理
  setLoginErrorMessages(state, messages) {
    state.loginErrorMessages = messages
  },

}

// 非同期処理を行い、ミューテーションにcommitするアクション
// アクションの第一引数に、commit()などを持つコンテキストオブジェクトを渡す
const actions = {
  // ユーザー登録処理
  async register(context, data) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // 非同期処理でサーバーのAPIを呼び出し
    const response = await axios.post('/api/register', data)

    // API通信が成功した場合
    if (response.status === CREATED) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserミューテーションでuserステートを更新
      context.commit('setUser', response.data)
      return false
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)

    // バリデーションエラー時はエラーメッセージのステートを更新
    if (response.status === UNPROCESSABLE_ENTITY) {
      context.commit('setRegisterErrorMessages', response.data.errors)
    } else {
      // errorモジュールのsetCodeミューテーションでステータスを更新
      context.commit('error/setCode', response.status, { root: true })
      // 別のモジュールのミューテーションを commit する場合は第三引数に { root: true } を追加
    }
  },

  // ログイン処理
  async login(context, data) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    const response = await axios.post('/api/login', data)

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserミューテーションでuserステートを更新
      context.commit('setUser', response.data)
      return false
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)

    // バリデーションエラー時はエラーメッセージのステートを更新
    if (response.status === UNPROCESSABLE_ENTITY) {
      context.commit('setLoginErrorMessages', response.data.errors)
    } else {
      // errorモジュールのsetCodeミューテーションでステータスを更新
      context.commit('error/setCode', response.status, { root: true })
    }
  },

  // ログアウト処理
  async logout(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    const response = await axios.post('/api/logout')

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserミューテーションでuserステートをnullにする
      context.commit('setUser', null)
      return false
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    context.commit('error/setCode', response.status, { root: true })
  },

  // ログインユーザーチェック処理
  async currentUser(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    const response = await axios.get(`/api/user`)
    // 返却されたユーザー情報（未ログインの場合はnull）を格納
    const user = response.data || null

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserミューテーションでuserステートを更新
      context.commit('setUser', user)
      return false
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    context.commit('error/setCode', response.status, { root: true })
  }
}


// ストアオブジェクトとしてエクスポート
export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
