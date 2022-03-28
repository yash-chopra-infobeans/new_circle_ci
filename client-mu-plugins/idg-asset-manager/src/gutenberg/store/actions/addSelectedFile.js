const addSelectedFile = (state, { value }) => ({
  ...state,
  selectedFiles: [...state.selectedFiles, value],
});

export default addSelectedFile;
