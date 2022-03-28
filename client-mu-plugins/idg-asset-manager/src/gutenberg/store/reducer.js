import setFiles from './actions/setFiles';
import addFile from './actions/addFile';
import editFile from './actions/editFile';
import deleteFile from './actions/deleteFile';
import selectFile from './actions/selectFile';
import clearSelectedFile from './actions/clearSelectedFile';
import setParams from './actions/setParams';
import addSelectedFile from './actions/addSelectedFile';
import removeSelectedFile from './actions/removeSelectedFile';
import setSelectedFiles from './actions/setSelectedFiles';

const reducer = (
  state = {
    searchParams: {},
    files: [], // all files
    selectedFile: false, // currently selected file
    selectedFiles: [], // multiple selected files
  },
  action,
) => {
  switch (action.type) {
    case 'SET_FILES':
      return setFiles(state, action);
    case 'SET_SELECTED_FILES':
      return setSelectedFiles(state, action);
    case 'ADD_SELECTED_FILE':
      return addSelectedFile(state, action);
    case 'REMOVE_SELECTED_FILE':
      return removeSelectedFile(state, action);
    case 'SELECT_FILE':
      return selectFile(state, action);
    case 'CLEAR_SELECTED_FILE':
      return clearSelectedFile(state, action);
    case 'ADD_FILE':
      return addFile(state, action);
    case 'EDIT_FILE':
      return editFile(state, action);
    case 'DELETE_FILE':
      return deleteFile(state, action);
    case 'SET_PARAMS':
      return setParams(state, action);
    default:
      return state;
  }
};

export default reducer;
