const selectors = {
  getFiles(state) {
    return state.files;
  },
  getSelectedFile(state) {
    return state.selectedFile;
  },
  getSelectedFiles(state) {
    return state.selectedFiles;
  },
  getParams(state) {
    return state.searchParams;
  },
};

export default selectors;
