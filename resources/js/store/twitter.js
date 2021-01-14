import { OK } from '../utility'


// データを格納するステート
const state = {
  usersTwitter: null, //ログイン済みユーザーのTwitterアカウント情報を保持
  totalAutoFollow: 0, //自動フォローでの合計フォロー数
}

// ステートの値から状態を算出するゲッター
const getters = {
  check: state => !!state.usersTwitter,  //ログインチェック（二重否定で確実に真偽値を返す）
  usersTwitter: state => state.usersTwitter ? state.usersTwitter : '',
  totalAutoFollow: state => state.totalAutoFollow ? state.totalAutoFollow : '',
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
  // userステートの値を更新する処理
  setUsersTwitter(state, usersTwitter) {
    state.usersTwitter = usersTwitter
  },
  // userステートの値を更新する処理
  setTotalAutoFollow(state, totalAutoFollow) {
    state.totalAutoFollow = totalAutoFollow
  },
}

// 非同期処理を行い、ミューテーションにcommitするアクション
// アクションの第一引数に、commit()などを持つコンテキストオブジェクトを渡す
const actions = {
  // Twitter認証チェック
  async checkAuth(context) {
    // サーバーのAPIを呼び出し
    const response = await axios.get('/api/auth/twitter/check')
    console.log('Twitter認証チェック')

    // API通信が成功した場合
    if (response.status === OK) {
      // 返却されたユーザー情報を格納
      const usersTwitter = response.data.twitter_user
      const totalAutoFollow = response.data.follow_total
      // setUserミューテーションでuserステートを更新
      context.commit('setUsersTwitter', usersTwitter)
      context.commit('setTotalAutoFollow', totalAutoFollow)
      console.log(totalAutoFollow)
      return false
    }
  },
  // 自動フォロー利用開始
  async applyAutoFollow(context) {
    // サーバーのAPIを呼び出し
    const response = await axios.get('/api/autofollow/apply')
    // API通信が成功した場合
    if (response.status === OK) {
      console.log('自動フォロー利用開始')
      // setUserミューテーションでuserステートを更新
      context.commit('setUsersTwitter', response.data)
      console.log(usersTwitter)
      return false
    }
  },
  // 自動フォロー利用解除
  async cancelAutoFollow(context) {
    // サーバーのAPIを呼び出し
    const response = await axios.get('/api/autofollow/cancel')
    // API通信が成功した場合
    if (response.status === OK) {
      console.log('自動フォロー利用解除')
      // setUserミューテーションでuserステートを更新
      context.commit('setUsersTwitter', response.data)
      console.log(usersTwitter)
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
