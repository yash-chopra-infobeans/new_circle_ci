const removeSelectedFile = (state, { value }) => {
  const selectedFiles = [
    ...state.selectedFiles.filter(selectedFile => selectedFile.id !== value.id),
  ];

  return {
    ...state,
    selectedFiles,
  };
};

export default removeSelectedFile;
