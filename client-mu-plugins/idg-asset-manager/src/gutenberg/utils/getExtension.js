/**
 * Gets the extension of a file name.
 *
 * @param {string} filename The file name.
 * @return {string} The extension of the file name.
 */
const getExtension = (filename = '') => {
  const parts = filename.split('.');

  return parts[parts.length - 1];
};

export default getExtension;
