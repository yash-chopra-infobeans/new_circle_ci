export default (iterable) => {
  if (Object.fromEntries) {
    return Object.fromEntries(iterable);
  }

  return [...iterable].reduce((obj, [key, val]) => {
    // eslint-disable-next-line no-param-reassign
    obj[key] = val;
    return obj;
  }, {});
};
