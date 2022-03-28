/**
 * Try to parse JSON, otherwise return an empty object.
 * @param {String} string
 *
 * @returns {Object}
 */
const parseJSON = string => {
  try {
    return JSON.parse(string);
  } catch (e) {
    return {};
  }
};

export default parseJSON;
