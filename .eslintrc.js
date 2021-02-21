module.exports = {
    'env': {
        'browser': true,
        'es2021': true
    },
    'extends': [
        'eslint:recommended',
        'plugin:vue/recommended',
    ],
    'parserOptions': {
        'ecmaVersion': 12,
        'sourceType': 'module',
		'parser': 'babel-eslint'
    },
    'plugins': [
        'vue',
    ],
    'rules': {
        // // タグの途中で改行しない
		// "vue/html-closing-bracket-newline": [2, { "multiline": "never" }],
        // // インデントはスペース2個
        // 'indent': [
        //     'error',
        //     2
        // ],
        // // 改行はUNIX
        // 'linebreak-style': [
        //     'error',
        //     'unix'
        // ],
        // // 文字列はシングルクオートで囲む
        // 'quotes': [
        //     'error',
        //     'single'
        // ],
        // // セミコロンは使用しない
        // 'semi': [
        //     'error',
        //     'never'
        // ],
        // // かっこの中のスペースを削除
		// "space-in-parens": ["error", "never"],
        // // production環境ではconsole.logとdebugは警告
        // 'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        // 'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    }
}
