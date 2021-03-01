module.exports = function (api) {
  api.cache(true); // この設定関数をキャッシュする

  const presets = [["@babel/preset-env", {
    useBuiltIns: "usage",
    corejs: 3,
    targets: '> 0.25%, not dead',
  }]];
  const plugins = ["@babel/plugin-proposal-optional-chaining"]

  return {
    presets,
    plugins
  };
}
