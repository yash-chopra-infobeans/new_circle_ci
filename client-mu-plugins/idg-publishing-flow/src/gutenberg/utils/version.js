export const version = () => document.querySelector('body').classList.find(bclass => bclass.includes('version-5-4'));

export const container = () => {
  if (version) {
    return '.block-editor-block-list__layout';
  }

  return '.edit-post-layout__content';
};

export const editor = () => {
  if (version) {
    return '.block-editor-writing-flow';
  }

  return '.editor-writing-flow';
};

export default version;
