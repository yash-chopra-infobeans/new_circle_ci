import { EditorState, Modifier } from 'draft-js';

export default (editorState, variable) => {
  const selectionState = editorState.getSelection();
  const contentState = editorState.getCurrentContent();

  const modifiedContent = Modifier.insertText(
    contentState,
    selectionState,
    `{{${variable}}}`,
    null,
    null,
  );

  return EditorState.push(editorState, modifiedContent, editorState.getLastChangeType());
};
