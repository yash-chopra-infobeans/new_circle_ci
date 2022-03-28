/**
 * Checks that the passed name is a string and that it only contains
 * lowercase alphanumeric characters or dashes, and starts with a letter.
 *
 * @param {string} name
 * @param {string} label
 */
export const validateString = (name, label) => {
  if (typeof name !== 'string') {
    console.error(`${label} name must be a string!`); // eslint-disable-line no-console
    return false;
  }

  if (!/^[a-z][a-z0-9-]*$/.test(name)) {
    console.error( // eslint-disable-line no-console
      `${label} name must include only lowercase alphanumeric characters or dashes, and start with a letter. Example: "my-widget".`,
    );
    return false;
  }

  return true;
};

export default {
  validateString,
};
