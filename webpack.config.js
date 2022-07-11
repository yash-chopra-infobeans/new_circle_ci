const webpackConfig = require('./private/webpack/config');

module.exports = (env, arg) => webpackConfig(env, arg);
