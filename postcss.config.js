const autoprefixer = require('autoprefixer');
const pxtorem = require('postcss-pxtorem');
const pseudoEnter = require('postcss-pseudo-class-enter');
const reporter = require('postcss-reporter');
const msUnit = require('postcss-ms-unit');
const cssnano = require('cssnano');

module.exports = (context) => {
  const env = context.webpack.options.mode;

  const plugins = [
    autoprefixer(),
    msUnit(),
    pxtorem({
      prop_white_list: ['font', 'font-size', 'line-height', 'letter-spacing'],
    }),
    pseudoEnter(),
    reporter({ clearMessages: true }),
  ];

  if (env === 'production') {
    plugins.push(cssnano());
  }

  return { plugins };
};
