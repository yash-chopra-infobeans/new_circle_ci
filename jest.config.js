module.exports = {
  transform: {
    '^.+\\.jsx?$': 'babel-jest',
  },
  globals: {
    __DEV__: true,
    __TEST__: true,
  },
  verbose: true,
  testURL: 'http://localhost',
};
