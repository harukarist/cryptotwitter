const path = require("path"); // Node.jsのpath モジュールを読み込み
const VueLoaderPlugin = require("vue-loader/lib/plugin"); // vue-loader@15から必要
const MiniCssExtractPlugin = require('mini-css-extract-plugin'); //CSSファイル出力プラグイン

// 'production'か'development'を指定
const MODE = process.env.NODE_ENV || 'production';

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = true;

module.exports = {
  mode: MODE,
  devtool: "source-map", // CSSの元ソースを追跡できるようsource-map方式にする
  entry: "./resources/js/app.js", // エントリポイントのファイル
  plugins: [
    // CSSファイルの出力先とファイル名
    new MiniCssExtractPlugin({ filename: './public/css/app.css' }),
  ],
  output: {
    // jsファイルの出力先ディレクトリ
    path: path.resolve(__dirname, "./public/js"),
    // 出力ファイル名
    filename: "app.js",
  },
  // ローカル開発用環境を立ち上げる
  // 実行時にブラウザが自動的に localhost を開く
  devServer: {
    contentBase: "public",
    open: true // 自動的にブラウザが立ち上がる
  },
  target: ['web', 'es5'], //IE対応
  module: {
    rules: [
      {
        // .vueファイルの設定
        test: /\.vue$/,
        use: [
          {
            loader: "vue-loader",
            options: {
              loaders: {
                js: 'babel-loader',
                scss: 'vue-style-loader!css-loader!sass-loader', // <style lang="scss">
                sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax' // <style lang="sass">
              }
            }
          },
        ]
      },
      // .jsファイルの設定
      {
        test: /\.js$/,
        exclude: /node_modules/, //node_modulesは除く
        use: [
          {
            loader: "babel-loader",
            // options: {
            //   presets: [
            //     // プリセットを指定して、ES2020を ES5 に変換
            //     "@babel/preset-env",
            //   ],
            // },
          },
        ]
      },
      // Sassの設定
      {
        test: /\.(sa|sc|c)ss$/,
        exclude: /node_modules/, //node_modulesは除く
        use: [  //後ろのモジュールから順に適用する
          // MiniCssExtractPluginでCSSファイルを分離する
          {
            loader: MiniCssExtractPlugin.loader
          },
          // style-loaderでlinkタグにCSSを展開
          // {
          //   loader: 'style-loader'
          // },
          // css-loaderでCSSをJSモジュールに変換
          {
            loader: "css-loader",
            options: {
              // CSS内のurl()メソッドの取り込みを禁止する
              url: false,
              // ソースマップの利用有無
              sourceMap: enabledSourceMap,

              // importLoadersで使用するloaderを指定
              // loaderを使わない場合は0,
              // postcss-loaderの使う場合は1,
              // postcss-loaderとsass-loaderを使う場合は2を指定
              importLoaders: 2
            }
          },
          // postcss-loaderで旧バージョンのブラウザにモダンCSSを適用
          {
            loader: "postcss-loader",
            options: {
              // ソースマップの利用有無
              sourceMap: enabledSourceMap,
              postcssOptions: {
                plugins: [
                  // Autoprefixerを有効に
                  // ベンダープレフィックスを自動付与する
                  ["autoprefixer", { grid: true }],
                ],
              },
            },
          },
          // sass-loaderでSassをCSSに変換
          {
            loader: "sass-loader",
            options: {
              // ソースマップの利用有無
              sourceMap: enabledSourceMap
            },
          },
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
