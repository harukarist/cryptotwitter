const { dest, watch, series, parallel } = require("gulp");
// 直列処理(順番に処理):series(), 並列処理（順番を問わない）:parallel()
// const sass = require('gulp-sass');
// const plumber = require('gulp-plumber');
// const postcss = require('gulp-postcss');
// const autoprefixer = require('autoprefixer');
// const cssdeclsort = require('css-declaration-sorter');
// const sassGlob = require('gulp-sass-glob'); // @importを纏めて指定
const browserSync = require('browser-sync');
// const gcmq = require('gulp-group-css-media-queries'); // media queryを纏める
// const mode = require('gulp-mode')({
//   modes: ['production', 'development'], // 本番実装時は 'gulp --production'
//   default: 'development',
//   verbose: false,
// })
const webpack = require("webpack");
const webpackStream = require("webpack-stream"); // gulpでwebpackを使うために必要なプラグイン

// webpack設定ファイルの読み込み
const webpackConfig = require("./webpack.config");

// const compileSass = done => {
//   const postcssPlugins = [
//     autoprefixer({
//       // browserlistはpackage.jsonに記載
//       cascade: false,
//       grid: 'autoplace' // IE11のgrid対応('-ms-')
//     }),
//     cssdeclsort({ order: 'alphabetical' /* smacss, concentric-css */ })
//   ]

//   src("./resources/sass/app.scss", { sourcemaps: true  /* init */ })
//     .pipe(plumber())
//     .pipe(sassGlob())
//     .pipe(sass({ outputStyle: 'expanded' }))
//     .pipe(postcss(postcssPlugins))
//     .pipe(mode.production(gcmq()))
//     .pipe(dest("./public/css/app.css", { sourcemaps: './sourcemaps' /* write */ }));
//   done(); // 終了宣言
// }

// ローカルサーバ起動
const buildServer = done => {
  browserSync.init({
    port: 8080,
    notify: false,
    // 静的サイト
    server: {
      baseDir: './',
    },
    // 動的サイト
    files: ['./public/*.php'],
    proxy: 'http://localhost',
  })
  done()
}

// ブラウザ自動リロード
const browserReload = done => {
  browserSync.reload()
  done()
}

// webpack
const bundleJs = () => {
  // webpackStreamの第2引数にwebpackを渡す
  return webpackStream(webpackConfig, webpack)
    .pipe(dest("./public/js"))
    .pipe(dest("./public/css"));
};

// ファイル監視
const watchFiles = () => {
  watch('./resources/sass/app.scss', series(bundleJs, browserReload))
  watch('./resources/js/app.scss', series(bundleJs, browserReload))
}

// exports.sass = compileSass;
exports.default = parallel(buildServer, watchFiles);
