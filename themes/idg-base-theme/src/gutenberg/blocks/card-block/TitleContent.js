const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { Button } = wp.components;

export default ({ className, onChange = () => {}, onDelete = () => {}, ...props }) => {
  const { value } = props;

  const handleChange = nextValue => {
    onChange(nextValue);
  };

  return (
    <div className="card-title-content">
      <RichText
        className="card-content-eyebrow"
        value={value.card_content_eyebrow}
        placeholder={__('Add eyebrow', 'idg-base-theme')}
        multiline={() => false}
        onChange={eyebrowValue => handleChange({ card_content_eyebrow: eyebrowValue })}
      />
      <RichText
        className="card-title"
        multiline={() => false}
        placeholder={__('Add your title', 'idg-base-theme')}
        value={value.card_content_title}
        onChange={titleValue => handleChange({ card_content_title: titleValue })}
      />
      <RichText
        className="card-content"
        tagName="p"
        placeholder={__('Add your text here', 'idg-base-theme')}
        value={value.card_content_text}
        onChange={textValue => handleChange({ card_content_text: textValue })}
      />
      <Button className="delete-card-button" onClick={onDelete}>
        <span className="dashicons dashicons-remove" aria-label="Remove item"></span>
      </Button>
      <hr />
    </div>
  );
};
