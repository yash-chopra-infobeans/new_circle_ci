import { Editor, EditorState, RichUtils, ContentState, convertFromHTML } from 'draft-js';
import { stateToHTML } from 'draft-js-export-html';

import InlineStyleControls from './InlineControls';

const { useState, useEffect, useRef } = wp.element;

const RichText = ({ value = '', onChange, placeholder = '' }) => {
  const input = useRef(null);

  const [editorState, setEditorState] = useState(EditorState.createEmpty());

  useEffect(() => {
    const blocksFromHTML = convertFromHTML(value);
    const state = ContentState.createFromBlockArray(
      blocksFromHTML.contentBlocks,
      blocksFromHTML.entityMap,
    );

    setEditorState(EditorState.createWithContent(state));
  }, []);

  const toggleInlineStyle = inlineStyle => {
    setEditorState(RichUtils.toggleInlineStyle(editorState, inlineStyle));
  };

  const getBlockStyle = block => {
    switch (block.getType()) {
      case 'blockquote':
        return 'RichEditor-blockquote';
      default:
        return null;
    }
  };

  let className = 'RichEditor-editor';

  const contentState = editorState.getCurrentContent();

  if (!contentState.hasText()) {
    if (contentState.getBlockMap().first().getType() !== 'unstyled') {
      className += ' RichEditor-hidePlaceholder';
    }
  }

  return (
    <div className="RichEditor-root">
      <InlineStyleControls editorState={editorState} onToggle={toggleInlineStyle} />
      <div className={className} onClick={() => input.current.focus()}>
        <Editor
          blockStyleFn={getBlockStyle}
          editorState={editorState}
          onChange={newValue => {
            setEditorState(newValue);
            onChange(stateToHTML(editorState.getCurrentContent()));
          }}
          placeholder={placeholder}
          ref={input}
        />
      </div>
    </div>
  );
};

export default RichText;
