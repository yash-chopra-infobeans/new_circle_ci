/**
 * Check if an array is empty, but first filter any falsy values from the array.
 * @param {Array} array - The array to check
 */
export const isEmpty = array => array.filter(item => item).length === 0;

/**
 * Delete the index of null values in an array.
 * @param {Array} array - The array to check
 */
export const deleteNullValues = (array) => {
  const clonedArray = [...array];

  clonedArray.forEach((item, index) => {
    if (item === null) {
      delete clonedArray[index];
    }
  });

  return clonedArray;
};

/**
 * Callback determining a specific condition.
 *
 * @callback conditionCallback
 * @param item - The current item of an array in question.
 *
 * @returns {Boolean}
 */

/**
 * Move up and down through an array from a starting point to
 * find the start and end indexes that meet a certain condition.
 * @param {Array} array - The array to traverse
 * @param {Number} index - The starting index
 * @param {conditionCallback} callback - A callback to run
 *
 * @return {Object} chunk
 * @return {Number} chunk.start - Start index
 * @return {Number} chunk.end - End index
 * @return {Array} chunk.part - The array chunk
 */
export const findArrayChunk = (array, index, condition) => {
  const firstHalf = array.slice(0, index).reverse();
  const secondHalf = array.slice(index, array.length);

  const start = firstHalf.findIndex(condition);
  const end = secondHalf.findIndex(condition);

  const startIndex = start === -1 ? 0 : index - start;
  const endIndex = end === -1 ? array.length : index + end;

  return {
    start: startIndex,
    end: endIndex,
    part: array.slice(startIndex, endIndex),
  };
};
