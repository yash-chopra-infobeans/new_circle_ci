const setFiles = (state, { value = [] }) => ({
  ...state,
  files: value,
});

export default setFiles;
