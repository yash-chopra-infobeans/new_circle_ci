import Button from './Button';

const InlineControls = ({ editorState, onToggle }) => {
  const currentStyle = editorState.getCurrentInlineStyle();

  const inlineStyles = [
    { label: 'Bold', style: 'BOLD' },
    { label: 'Italic', style: 'ITALIC' },
    { label: 'Underline', style: 'UNDERLINE' },
  ];

  return (
    <div className="RichEditor-controls">
      {inlineStyles.map(type => (
        <Button
          key={type.label}
          active={currentStyle.has(type.style)}
          label={type.label}
          onToggle={onToggle}
          style={type.style}
        />
      ))}
    </div>
  );
};

export default InlineControls;
