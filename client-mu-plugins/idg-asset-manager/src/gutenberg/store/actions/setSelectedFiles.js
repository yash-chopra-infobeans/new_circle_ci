const setSelectedFiles = (state, { value = [] }) => ({
  ...state,
  selectedFiles: value,
});

export default setSelectedFiles;
