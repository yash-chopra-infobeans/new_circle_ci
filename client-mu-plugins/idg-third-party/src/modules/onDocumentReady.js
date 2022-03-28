/**
 * Fire a callback when the dom has loaded.
 * @param {*} callback
 */
const onDocumentReady = callback => {
  if (
    document.readyState === 'complete' ||
    (document.readyState !== 'loading' && !document.documentElement.doScroll)
  ) {
    callback();
    return;
  }

  document.addEventListener('DOMContentLoaded', callback);
};

export default onDocumentReady;
