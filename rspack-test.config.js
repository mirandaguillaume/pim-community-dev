/* eslint-env es6 */
const path = require('path');
const rspack = require('@rspack/core');
const prodConfig = require('./rspack.config.js');

const config = Object.assign({}, prodConfig, {
  entry: [
    'core-js/stable',
    'regenerator-runtime/runtime',
    path.resolve(__dirname, './tests/front/common/templates/index.js'),
  ],
  output: {
    path: path.resolve('./public/test_dist/'),
    publicPath: '/dist/',
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js',
  },
});

config.plugins = [
  ...config.plugins,
  new rspack.HtmlRspackPlugin({
    inject: 'head',
    template: path.resolve(__dirname, './tests/front/common/templates/index.html'),
    minify: false,
  }),
];

module.exports = config;
