const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
require('dotenv').config();
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const environment = process.env.NODE_ENV || 'development';
const isDevelopment = environment === 'development';
const publicPath = `/themes/${process.env.WP_THEME_NAME}`;

module.exports = {
  entry: {
    main: `${__dirname}/src/js/main.ts`,
  },
  target: 'web',
  mode: isDevelopment ? environment : 'production',
  devtool: isDevelopment ? 'inline-source-map' : false,
  resolve: {
    extensions: ['.ts', '.tsx', '.js'],
  },
  output: {
    path: isDevelopment ? `${__dirname}/public${publicPath}` : `${__dirname}/dist${publicPath}`,
    publicPath: publicPath,
    filename: 'js/[name].js',
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.ts?$/,
        use: [
          {
            loader: 'eslint-loader',
            options: {
              fix: true,
              failOnError: true,
            },
          },
        ],
      },
      {
        test: /\.ts|js$/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
            },
          },
          { loader: 'ts-loader' },
        ],
        exclude: [/node_modules\/(?!(swiper|dom7|axios|has-flag|supports-color)\/).*/],
      },
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: isDevelopment,
              url: false,
              importLoaders: 2,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: isDevelopment,
              postcssOptions: {
                plugins: [require('autoprefixer')],
              },
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: isDevelopment,
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new webpack.DefinePlugin({
      NODE_ENV: JSON.stringify(environment),
    }),
    new MiniCssExtractPlugin({
      filename: 'css/style.css',
    }),
    new BrowserSyncPlugin({
      proxy: `localhost:${process.env.LOCAL_PORT}`,
      files: ['public/themes/**/*.php'],
    }),
  ],
};
