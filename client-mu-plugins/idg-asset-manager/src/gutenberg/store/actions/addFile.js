const addFile = (state, { value = [] }) => ({
  ...state,
  files: [value, ...state.files],
});

export default addFile;
