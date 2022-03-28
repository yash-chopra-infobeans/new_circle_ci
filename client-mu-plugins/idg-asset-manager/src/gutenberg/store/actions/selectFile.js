const selectFile = (state, { value }) => ({
  ...state,
  selectedFile: state.files.find(file => `${file.id}` === `${value}`),
});

export default selectFile;
