/* eslint-disable import/prefer-default-export */
/**
 * Map sizes defined in `750x30,540x30' format to array of arrays.
 *
 * @param {string} sizes
 *
 * @returns {string}
 */
export const extractSizes = sizes => {
  const sizeDefinitions = sizes.split(',');

  return sizeDefinitions.map(size => {
    return size
      .split('x')
      .map(unit => parseInt(unit, 10))
      .filter(unit => !Number.isNaN(unit));
  });
};
