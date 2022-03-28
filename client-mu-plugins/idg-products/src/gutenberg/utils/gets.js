import { get } from 'lodash-es';

/**
 * Get an object key value recursively if first instance isn't found.
 * @param {object} object - The object
 * @param {array} keys - Array of keys to search
 */
const gets = (object, keys) => {
  if (keys.length > 1) {
    return get(object, keys.pop()) || gets(object, keys);
  }

  return get(object, keys[0]);
};

export default gets;
