import RichTextEditor from 'react-rte';
import { isEqual } from 'lodash-es';

const { useState, useEffect } = wp.element;

const toolbarConfig = {
  // Optionally specify the groups to display (displayed in the order listed).
  display: ['INLINE_STYLE_BUTTONS', 'BLOCK_TYPE_BUTTONS', 'LINK_BUTTONS'],
  INLINE_STYLE_BUTTONS: [
    { label: 'Bold', style: 'BOLD' },
    { label: 'Italic', style: 'ITALIC' },
    { label: 'Underline', style: 'UNDERLINE' },
  ],
  BLOCK_TYPE_DROPDOWN: [
    { label: 'Normal', style: 'unstyled' },
    { label: 'Heading Large', style: 'header-one' },
    { label: 'Heading Medium', style: 'header-two' },
    { label: 'Heading Small', style: 'header-three' },
  ],
  BLOCK_TYPE_BUTTONS: [
    { label: 'UL', style: 'unordered-list-item' },
    { label: 'OL', style: 'ordered-list-item' },
  ],
};

const RichText = ({ value, onChange }) => {
  const [editorValue, setEditorValue] = useState(
    value ? RichTextEditor.createValueFromString(value, 'html') : RichTextEditor.createEmptyValue(),
  );

  const shouldUpdate = () => {
    return !isEqual(editorValue.toString('html'), value);
  };

  useEffect(() => {
    onChange(editorValue.toString('html'));
  }, [shouldUpdate()]);

  return (
    <div>
      <RichTextEditor
        toolbarConfig={toolbarConfig}
        value={editorValue}
        onChange={newValue => {
          setEditorValue(newValue);

          if (onChange) {
            onChange(newValue.toString('html'));
          }
        }}
      />
    </div>
  );
};

export default RichText;
