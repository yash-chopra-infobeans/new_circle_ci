const editFile = (state, { value, ID }) => ({
  ...state,
  files: state.files.map(file => (file.id === ID ? { ...file, ...value } : file)),
  selectedFile:
    state.selectedFile && state.selectedFile.id === ID
      ? { ...state.selectedFile, ...value }
      : state.selectedFile,
});

export default editFile;
