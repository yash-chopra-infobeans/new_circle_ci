const path = require('path');
const webpack = require('webpack');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');

const getEntryPoints = require('./utils/getEntryPoints.js');
const { findProjectPath, findAllProjectPaths } = require('./utils/findProjectPath.js');
const assetSettingsTemplate = require('./templates/asset-settings.js');

const ROOT_DIR = 'idg-content-hub';
const DEFAULT_PROJECT = 'idg-base-theme';

const compileProjects = (PROJECT_PATH, mode, projectName) => {
  const SRC_PATH = `${PROJECT_PATH}/src`;

  const USE_PLUGIN = {
    CLEAN: mode === 'production',
    COPY: true,
  };

  const PATHS_TO_CLEAN = [`${PROJECT_PATH}/dist/scripts/**/*`, `${PROJECT_PATH}/dist/styles/**/*`];

  const fileLoaderOptions = {
    name: '[path][name].[ext]',
    emitFile: false, // Don't emit, using copy function to copy files over.
    outputPath: '../', // or // publicPath: '../'.
    context: SRC_PATH,
  };

  return {
    entry: getEntryPoints(SRC_PATH),

    output: {
      filename: mode === 'production' ? '[name]-[hash:8].js' : '[name].js',
      path: `${PROJECT_PATH}/dist/scripts`,
    },

    externals: {
      react: 'React',
      'react-dom': 'ReactDOM',
      jquery: 'jQuery',
    },

    watchOptions: {
      ignored: ['node_modules'],
    },

    performance: {
      assetFilter: assetFilename => /\.(js|css)$/.test(assetFilename),
      maxEntrypointSize: 20000000, // Large entry point size as we only need asset size. (2mb)
      maxAssetSize: 500000, // Set max size to 500kb.
    },

    devtool: mode === 'production' ? 'source-map' : 'inline-cheap-module-source-map',

    stats: {
      builtAt: true,
      entrypoints: false,
      modules: false,
      children: false,
      excludeAssets: 'static', // Hide the copied static files from the output:
    },

    plugins: [
      new webpack.ExtendedAPIPlugin(),

      // Sets mode so we can access it in `postcss.config.js`.
      new webpack.LoaderOptionsPlugin({
        options: { mode },
      }),

      new HtmlWebpackPlugin({
        excludeChunks: ['static'],
        filename: `${PROJECT_PATH}/inc/asset-settings.php`,
        inject: false,
        minify: false,
        templateContent: config => assetSettingsTemplate(config, projectName, mode),
      }),

      // Extract CSS to own bundle, filename relative to output.path.
      new MiniCssExtractPlugin({
        filename:
          // or ../styles/[name].css for dynamic name
          //mode === 'production' ? '../styles/[name]-[contenthash:8].css' : '../styles/[name].css',
          '../styles/[name].css',
        chunkFilename: '[name].css',
      }),

      // Lint SCSS.
      new StyleLintPlugin({
        syntax: 'scss',
        context: SRC_PATH,
      }),

      // Global vars for checking dev environment.
      new webpack.DefinePlugin({
        __DEV__: JSON.stringify(mode === 'development'),
        __PROD__: JSON.stringify(mode === 'production'),
        __TEST__: JSON.stringify(process.env.NODE_ENV === 'test'),
      }),

      new webpack.BannerPlugin(
        `Copyright (c) ${new Date().getFullYear()} Big Bite® | bigbite.net | @bigbite`,
      ),

      new SpriteLoaderPlugin({
        plainSprite: true,
      }),

      USE_PLUGIN.CLEAN &&
        new CleanWebpackPlugin({
          verbose: true,
          cleanOnceBeforeBuildPatterns: PATHS_TO_CLEAN,
          dangerouslyAllowCleanPatternsOutsideProject: true,
          dry: false,
        }),

      USE_PLUGIN.COPY &&
        new CopyWebpackPlugin([
          {
            from: `${SRC_PATH}/static/**/*`,
            to: `${PROJECT_PATH}/dist/`,
            cache: false,
            context: SRC_PATH,
          },
        ]),
    ].filter(Boolean),

    module: {
      rules: [
        {
          exclude: [/node_modules\/(?!(swiper|dom7)\/).*/, /\.test\.jsx?$/],
          use: [{ loader: 'babel-loader' }],
        },

        {
          test: /\.(png|woff|woff2|eot|ttf|gif|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
          loader: 'file-loader',
          options: fileLoaderOptions,
        },

        {
          test: /\.svg$/,
          use: 'svg-sprite-loader',
        },

        {
          test: /\.(sa|sc|c)ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                sourceMap: true,
              },
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: true,
              },
            },
            {
              loader: 'resolve-url-loader',
              options: {
                debug: false,
                sourceMap: true,
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: true,
              },
            },
          ],
        },

        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: ['babel-loader', 'eslint-loader'],
        },
      ],
    },
  };
};

module.exports = (_env, { mode, project = false, allProjects = false }) => {
  let projectName = project;

  if (!projectName && process.env.INIT_CWD) {
    const { name } = path.parse(process.env.INIT_CWD);
    projectName = name === ROOT_DIR ? DEFAULT_PROJECT : name;
  }

  let PROJECT_PATHS = [];

  // Find all.
  if (allProjects) {
    PROJECT_PATHS = findAllProjectPaths();
  } else {
    projectName.split(',').forEach(projectItem => {
      const foundProject = findProjectPath(projectItem);
      if (!foundProject) throw new Error(`Project ${projectItem} does not exist.`);
      PROJECT_PATHS.push(foundProject);
    });
  }

  if (PROJECT_PATHS.length <= 0) {
    throw new Error("Can't find project files.");
  }

  const COMPILERS = [];

  PROJECT_PATHS.forEach(PROJECT_PATH => {
    COMPILERS.push(compileProjects(PROJECT_PATH, mode, path.basename(PROJECT_PATH)));
  });

  return COMPILERS;
};
