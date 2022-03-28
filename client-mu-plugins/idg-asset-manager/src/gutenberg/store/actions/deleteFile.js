const deleteFile = (state, { value }) => ({
  ...state,
  files: state.files.filter(file => file.id !== value),
  selectedFile: state?.selectedFile?.id === value ? false : state.selectedFile,
});

export default deleteFile;
