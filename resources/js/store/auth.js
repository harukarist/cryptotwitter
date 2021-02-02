import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../utility'

// ユーザーの認証状態を管理するauthモジュール
// ページを遷移してもログイン状態を保持するため、Vuexで状態管理を行う

// データを格納するステート
const state = {
  userData: null, //ログイン済みユーザーの情報を保持
  apiStatus: null, //API呼び出しの成否を格納
  registerErrorMessages: null, //ユーザー登録エラーメッセージを格納
  loginErrorMessages: null, //ログインエラーメッセージを格納
  editErrorMessages: null, //ユーザー情報編集エラーメッセージを格納
}

// ステートの値から状態を算出するゲッター
const getters = {
  // ユーザーが認証済みかどうかをチェックする
  check: state => !!state.userData, //ログインチェック（二重否定で確実に真偽値を返す）
  userName: state => state.userData ? state.userData.name : '', //ユーザー名を格納（nullの場合は空文字を返す）
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
  // userDataステートの値を更新する処理
  setUserData(state, userData) {
    state.userData = userData
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
  // editErrorMessagesステートを更新する処理
  setEditErrorMessages(state, messages) {
    state.editErrorMessages = messages
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
    // 通信失敗時のcatch処理はbootstrap.jsでaxiosのインターセプターにまとめて記載
    // .catch(err => err.response || err)

    // API通信が成功した場合
    if (response.status === CREATED) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUserData', response.data)
      return false  //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)

    // バリデーションエラー時はエラーメッセージのステートを更新
    if (response.status === UNPROCESSABLE_ENTITY) {
      context.commit('setRegisterErrorMessages', response.data.errors)
    } else {
      // errorモジュールのsetCodeミューテーションでステータスを更新
      // 別モジュールのミューテーションをcommitするためroot: trueを指定する
      context.commit('error/setCode', response.status, { root: true })
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
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUserData', response.data)
      // dispatch()でtwitterストアのcheckAuthアクションを呼び出す
      // 別モジュールのアクションを呼び出すため、第三引数にroot: trueを指定する
      context.dispatch("twitter/checkAuth", '', { root: true });

      return false //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)

    // バリデーションエラー時はエラーメッセージのステートを更新
    if (response.status === UNPROCESSABLE_ENTITY) {
      context.commit('setLoginErrorMessages', response.data.errors)
    } else {
      // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
      // 別モジュールのミューテーションをcommitするためroot: trueを指定する
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
      // setUserDataミューテーションでuserDataステートをnullにする
      context.commit('setUserData', null)
      // twitterストアのステートをnullにする
      context.commit('twitter/setUsersTwitter', null, { root: true })
      context.commit('twitter/setTotalAutoFollow', null, { root: true })

      return false //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  // ログインチェック処理
  async currentUser(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    const response = await axios.post('/api/user')
    // 返却されたユーザー情報（未ログインの場合はnull）を格納
    const data = response.data || null
    console.log('currentUser')
    console.log(response)
    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUserData', data)
      return false //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  // ユーザー情報編集処理
  async changeAccount(context, data) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)

    // 非同期処理でサーバーのAPIを呼び出し
    const response = await axios.post('/api/account/change', data)
    console.log(response)
    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUserData', response.data)
      return false  //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)

    // バリデーションエラー時はエラーメッセージのステートを更新
    if (response.status === UNPROCESSABLE_ENTITY) {
      context.commit('setEditErrorMessages', response.data.errors)
    } else {
      // errorモジュールのsetCodeミューテーションでステータスを更新
      // 別モジュールのミューテーションをcommitするためroot: trueを指定する
      context.commit('error/setCode', response.status, { root: true })
    }
  },
  // ユーザー退会処理
  async withdraw(context, data) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)

    // 非同期処理でサーバーのAPIを呼び出し
    const response = await axios.post('/api/account/withdraw', data)

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUserData', null)
      return false  //処理を終了
    }
    // API通信が失敗した場合はステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })

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
