import axios from 'axios'
import { OK } from '../utility'


// データを格納するステート
const state = {
  usersTwitter: null, //ログイン済みユーザーのTwitterアカウント情報を保持
  apiStatus: null, //API呼び出しの成否を格納
}

// ステートの値から状態を算出するゲッター
const getters = {
  check: state => !!state.usersTwitter,  //ログインチェック（二重否定で確実に真偽値を返す）
  usersTwitter: state => state.usersTwitter ? state.usersTwitter : '',
  usersAvatar: state => state.usersTwitter ? state.usersTwitter.twitter_avatar : '/img/avatar_noimage.png',
  useAutoFollow: state => state.usersTwitter ? state.usersTwitter.use_autofollow : false,
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
  // userDataステートの値を更新する処理
  setUsersTwitter(state, usersTwitter) {
    state.usersTwitter = usersTwitter
  },
  // userDataステートの値を更新する処理
  setUseAutoFollow(state, status) {
    state.usersTwitter.use_autofollow = status
  },
  // apiStatusステートを更新する処理
  setApiStatus(state, status) {
    state.apiStatus = status
  },
}

// 非同期処理を行い、ミューテーションにcommitするアクション
const actions = {
  /**
   * Twitter認証チェック
   */
  // アクションの第一引数に、commit()などを持つコンテキストオブジェクトを渡す
  async checkAuth(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    context.commit("loader/setIsLoading", true, { root: true }); //ローディング表示をオン
    const response = await axios.post('/api/auth/twitter/check')
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオフ
    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // 返却されたユーザー情報（未ログインの場合はnull）を格納
      const usersTwitter = response.data || null
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUsersTwitter', usersTwitter)
      return false //処理を終了
    }
    // API通信が失敗した場合
    // ステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  /**
 * ログインユーザーのTwitterアカウント情報とフォロー済みリストを更新
 */
  // アクションの第一引数に、commit()などを持つコンテキストオブジェクトを渡す
  async updateTwitterUser(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)
    // サーバーのAPIを呼び出し
    context.commit("loader/setIsLoading", true, { root: true }); //ローディング表示をオン
    const response = await axios.post('/api/auth/twitter/update')
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオフ
    console.log('twitterUpdate');
    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // 返却されたユーザー情報（未ログインの場合はnull）を格納
      const usersTwitter = response.data || null
      // setUserDataミューテーションでuserDataステートを更新
      context.commit('setUsersTwitter', usersTwitter)
      return false //処理を終了
    }
    // API通信が失敗した場合
    // ステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  /**
   * Twitterアカウント連携を削除
   */
  async deleteAuth(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)

    // サーバーのAPIを呼び出し
    context.commit("loader/setIsLoading", true, { root: true }); //ローディング表示をオン
    const response = await axios.post('/api/auth/twitter/delete')
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオフ

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // ユーザー情報のステートをnullに初期化
      context.commit('setUsersTwitter', null)
      return false //処理を終了
    }
    // API通信が失敗した場合
    // ステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  /**
   * 自動フォロー利用開始
   */
  async applyAutoFollow(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)

    // サーバーのAPIを呼び出し
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオン
    const response = await axios.post('/api/autofollow/apply')
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオフ
    // console.log(response);

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // 自動フォロー利用フラグをtrueに更新
      context.commit('setUseAutoFollow', true)
      return false
    }
    // API通信が失敗した場合
    // ステータスをfalseに変更
    context.commit('setApiStatus', false)
    // errorモジュールのsetCodeミューテーションでステータスを更新
    // 別モジュールのミューテーションをcommitするためroot: trueを指定する
    context.commit('error/setCode', response.status, { root: true })
  },

  /**
   * 自動フォロー利用解除
   */
  async cancelAutoFollow(context) {
    // setApiStatusミューテーションでステータスを初期化
    context.commit('setApiStatus', null)

    // サーバーのAPIを呼び出し
    context.commit("loader/setIsLoading", true, { root: true }); //ローディング表示をオン
    const response = await axios.post('/api/autofollow/cancel')
    context.commit("loader/setIsLoading", false, { root: true }); //ローディング表示をオフ
    // console.log(response);

    // API通信が成功した場合
    if (response.status === OK) {
      // setApiStatusミューテーションでステータスをtrueに変更
      context.commit('setApiStatus', true)
      // 自動フォロー利用フラグをfalseに更新
      context.commit('setUseAutoFollow', false)
      return false
    }
    // API通信が失敗した場合
    // ステータスをfalseに変更
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
