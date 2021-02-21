module.exports = {
	'env': {
		'browser': true,
		'es6': true
	},
	'extends': [
		'eslint:recommended',
		'plugin:vue/recommended',
	],
	'parserOptions': {
		'ecmaVersion': 2018,
		'sourceType': 'module'
	},
	'plugins': [
		'vue'
	],
	'rules': {
		// タグの途中で改行しない
		"vue/html-closing-bracket-newline": [2, { "multiline": "never" }],
		// 無駄なスペースは削除
		"no-multi-spaces": 2,
		// かっこの中はスペースなし
		"space-in-parens": [2, "never"],
		'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
		'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',

		// "camelcase": "off",
		// "comma-dangle": "off",
		// "comma-spacing": "off",
		// "eqeqeq": "off",
		// "handle-callback-err": "off",
		// "indent": "off",
		// "key-spacing": "off",
		// "keyword-spacing": "off",
		// "no-multi-spaces": "off",
		// "no-undef": "off",
		// "no-unused-vars": "off",
		// "object-curly-spacing": "off",
		// "quotes": "off",
		// "semi": "off",
		// "space-before-function-paren": "off",
		// "space-before-blocks": "off",
		// "space-in-parens": "off",
		// "spaced-comment": "off",
		// "space-infix-ops": "off",
		// "no-dupe-keys": "off",
		// "no-fallthrough": "off",
		// "no-spaced-func": "off",
		// "no-multiple-empty-lines": "off",
		// "no-trailing-spaces": "off",
		// "padded-blocks": "off",

		// // 追加
		// "no-useless-escape": "off",
		// "no-constant-condition": "off",
		// "no-empty": "off",
		// "no-extra-semi": "off",
		// "no-prototype-builtins": "off",
		// "consistent-return": "off",
		// "no-sparse-arrays": "off",
		// "no-control-regex": "off",
		// "no-self-assign": "off",
		// "no-unsafe-finally": "off",
		// "no-redeclare": "off",
		// "no-cond-assign": "off",
		// "no-func-assign": "off",
		// "func-style": "off",
		// "consistent-return": "off",
		// "getter-return": "off",
		// "no-unexpected-multiline": "off",
	}
}
