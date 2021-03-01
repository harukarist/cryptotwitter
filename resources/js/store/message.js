const state = {
  text: '',
  type: '',
  timeoutId: -1,
}

const mutations = {
// フラッシュメッセージのテキストとcssクラスを指定
  setMessage(state, { text, type }) {
    state.text = text
    state.type = type
  },
  // フラッシュメッセージのテキストとcssクラスをクリア
  clearMessage(state) {
    state.text = ''
    state.type = ''
    state.timeoutId = -1
  },
  // タイムアウトを管理するtimeoutIdをセット
  setTimeoutId(state, value) {
    state.timeoutId = value
  },
  // timeoutIdをクリア
  clearTimeoutId(state) {
    state.timeoutId = -1
  },
}

const actions = {
// フラッシュメッセージを表示
  showMessage(context, { text, type, timeout }) {
    if (state.timeoutId !== -1) {
      // timeoutIdが初期値以外の場合は1つ前のタイムアウトが実行中のため、実行中のtimeoutIdをclearTimeout()に渡して停止する
      clearTimeout(state.timeoutId)
      // timeoutIdのステートを初期化
      context.commit('clearTimeoutId')
    }

    // 状態の指定がない場合はsuccessに設定
    if (typeof type === 'undefined') {
      type = 'success'
    }
    // ミューテーションでステートのメッセージを更新
    context.commit('setMessage', { text, type })

    // タイムアウトの指定が0の場合はメッセージを非表示にしない
    if (timeout === 0) {
      return
    }

    // 引数の指定がない場合はタイムアウトを3秒間に設定
    if (typeof timeout === 'undefined') {
      timeout = 5000
    }
    // 指定時間が経過したらメッセージを空にするタイムアウトを設定
    const timeoutId = setTimeout(() => {
      context.commit('clearMessage')
    }, timeout)
    // setTimeout()の戻り値timeoutIdをステートにセット
    context.commit('setTimeoutId', timeoutId)
  },

  // フラッシュメッセージを閉じる
  closeMessage(context) {
    context.commit('clearMessage')
  },
}

// ストアオブジェクトとしてエクスポート
export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
