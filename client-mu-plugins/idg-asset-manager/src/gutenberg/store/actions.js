const actions = {
  selectFile(value) {
    return {
      type: 'SELECT_FILE',
      value,
    };
  },
  clearSelectedFile() {
    return {
      type: 'CLEAR_SELECTED_FILE',
    };
  },
  setFiles(value) {
    return {
      type: 'SET_FILES',
      value,
    };
  },
  addFile(value) {
    return {
      type: 'ADD_FILE',
      value,
    };
  },
  editFile(value, ID) {
    return {
      type: 'EDIT_FILE',
      value,
      ID,
    };
  },
  deleteFile(value) {
    return {
      type: 'DELETE_FILE',
      value,
    };
  },
  setParams(value) {
    return {
      type: 'SET_PARAMS',
      value,
    };
  },
  setSelectedFiles(value) {
    return {
      type: 'SET_SELECTED_FILES',
      value,
    };
  },
  addSelectedFile(value) {
    return {
      type: 'ADD_SELECTED_FILE',
      value,
    };
  },
  removeSelectedFile(value) {
    return {
      type: 'REMOVE_SELECTED_FILE',
      value,
    };
  },
};

export default actions;
