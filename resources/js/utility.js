// ステータスコードの定義
export const OK = 200 //通信成功時
export const CREATED = 201 //ユーザー登録成功時
export const INTERNAL_SERVER_ERROR = 500 //サーバー内部エラー
export const UNPROCESSABLE_ENTITY = 422 //バリデーションエラー

/**
 * クッキーの値を取得する
 * @param {String} searchKey 検索するキー
 * @returns {String} キーに対応する値
 */
export function getCookieValue(searchKey) {
  if (typeof searchKey === 'undefined') {
    return ''
  }

  let val = ''

  // document.cookieで取得したクッキー（形式は name=12345;token=67890;key=abcde）を
  // ; で分割し、さらに = で分割して searchKeyと一致するkeyのvalueを取得
  document.cookie.split(';').forEach(cookie => {
    const [key, value] = cookie.split('=')
    if (key === searchKey) {
      return val = value
    }
  })

  return val
}
