// エラー情報を管理するerrorモジュール

// エラー情報を格納するステート
const state = {
  code: null, // エラーのステータスコードを格納
}

// ステートの値を同期処理で更新するミューテーション
// ミューテーションでは必ず第一引数にステートを指定する
const mutations = {
// codeステートの値を更新する処理
  setCode(state, code) {
    state.code = code
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
