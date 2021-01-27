const path = require("path"); // Node.jsのpath モジュールを読み込み
const VueLoaderPlugin = require("vue-loader/lib/plugin"); // vue-loader@15から必要
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: process.env.NODE_ENV || 'production',
  entry: "./resources/js/app.js", // エントリポイントのファイル
  output: {
    // 出力先のディレクトリ
    path: path.resolve(__dirname, "./public/js"),
    // 出力ファイル名
    filename: "app.js",
  },
  target: ['web', 'es5'], //IE対応
  module: {
    rules: [
      {
        // .vueファイルの場合はvue-loaderを使う
        test: /\.vue$/,
        loader: "vue-loader",
        options: {
          loaders: {
            js: 'babel-loader',
            scss: 'vue-style-loader!css-loader!sass-loader', // <style lang="scss">
            sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax' // <style lang="sass">
          }
        }
      },
      // Babelの設定
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: "babel-loader",
      },
      // Sassの設定
      {
        test: /\.(sa|sc|c)ss$/,
        exclude: /node_modules/,
        use: [  //後ろのモジュールから順に適用
          // CSSファイルを分離する
          MiniCssExtractPlugin.loader,
          {
            // css-loaderでCSSをJSモジュールに変換
            loader: 'css-loader',
            options: { url: false } //画像ファイルのurlは除く
          },
          {
            // sass-loaderでCSSに変換
            loader: 'sass-loader',
          },
          // {
          //   // 旧バージョンのブラウザにモダンCSSを適用
          //   loader: "postcss-loader",
          //   options: {
          //     plugins: [
          //       // ベンダープレフィックス自動付与
          //       require("autoprefixer")({
          //         grid: true,
          //       })
          //     ]
          //   }
          // },
        ]
      },
    ],
  },
  resolve: {
    // importするときに省略できる拡張子の設定
    extensions: [".js", ".vue"],
    alias: {
      // vue-template-compilerでのコンパイルに必要
      vue$: "vue/dist/vue.esm.js",
    },
  },
  plugins: [
    new VueLoaderPlugin(), //Vueを読み込むプラグイン
    new MiniCssExtractPlugin({
      filename: '../css/app.css',  //cssファイルの出力先
    })
  ],
};
