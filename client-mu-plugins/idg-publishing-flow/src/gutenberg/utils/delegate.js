/**
 * Return HTMLElement if the element supplied matches the selector,
 * if not it loops through all parents until it find a match.
 *
 * @param {HTMLElement} element - current target.
 * @param {string} selector - selector to check that the element matches.
 * @returns {HTMLElement|Boolean}
 */
export const delegate = (element, selector) => {
  // if element matches return element.
  if (element.matches(selector)) {
    return element;
  }

  // while there is a parentElement and the parent element
  // doesn't match the selector loop through parent selector
  while ((element = element.parentElement) && !element.matches(selector)); // eslint-disable-line

  // if element then return it.
  if (element) {
    return element;
  }

  // nothing matches, return false.
  return false;
};

export default delegate;
