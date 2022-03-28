/* eslint-disable */
/**
 * Groups items by key
 *
 * @param {Array[]} xs an array of items.
 * @param {string} key The key you want to group by.
 *
 * @return {Array[]} Grouped up array.
 */
export const groupBy = function group(xs, key) {
  return xs.reduce((rv, x) => {
    // eslint-disable-next-line no-param-reassign
    (rv[x[key]] = rv[x[key]] || []).push(x);
    return rv;
  }, {});
};
