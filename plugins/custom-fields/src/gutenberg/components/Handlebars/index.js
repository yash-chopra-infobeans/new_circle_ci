import { isEqual } from 'lodash-es';
import { Editor, EditorState, ContentState, convertFromHTML, Modifier } from 'draft-js';

import createDecorators from './decorators';
import Toolbar from './Toolbar';

const { useState, useRef, useEffect } = wp.element;

const Handlebars = ({ value, onChange, handlebars = [] }) => {
  const input = useRef(null);
  const decorators = createDecorators({ handlebars });

  const [editorState, setEditorState] = useState(EditorState.createEmpty(decorators));

  const shouldUpdate = () => {
    const contentState = editorState.getCurrentContent();
    const currentStateAsString = contentState.getPlainText();
    return !isEqual(currentStateAsString, value);
  };

  useEffect(() => {
    if (!value) {
      return;
    }

    const blocksFromHTML = convertFromHTML(value);

    const content = ContentState.createFromBlockArray(
      blocksFromHTML.contentBlocks,
      blocksFromHTML.entityMap,
    );

    setEditorState(EditorState.createWithContent(content, decorators));
  }, [shouldUpdate()]);

  const handleOnChange = newEditorState => {
    const contentState = newEditorState.getCurrentContent();

    setEditorState(newEditorState);

    onChange(contentState.getPlainText());
  };

  const handlePastedText = text => {
    const newContent = Modifier.insertText(
      editorState.getCurrentContent(),
      editorState.getSelection(),
      text,
    );

    handleOnChange(EditorState.push(editorState, newContent, 'insert-characters'));

    return true;
  };

  const isFocused = editorState.getSelection().getHasFocus();

  return (
    <div
      className={`cf-handlebars ${isFocused ? 'is-focused' : ''}`}
      onClick={() => input.current.focus()}
    >
      <Toolbar editorState={editorState} onChange={handleOnChange} handlebars={handlebars} />
      <div className="cf-handlebars-editor">
        <Editor
          editorState={editorState}
          onChange={handleOnChange}
          ref={input}
          handleReturn={() => 'handled'}
          handlePastedText={handlePastedText}
        />
      </div>
    </div>
  );
};

export default Handlebars;
