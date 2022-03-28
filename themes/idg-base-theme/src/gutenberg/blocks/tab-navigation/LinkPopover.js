const { __experimentalLinkControl } = wp.blockEditor;
const LinkControl = __experimentalLinkControl;

const { __ } = wp.i18n;
const { useState } = wp.element;
const { Button, Popover, TextControl } = wp.components;

export default ({
  className,
  onChange = () => {},
  onFocusOutside = () => {},
  onDelete = () => {},
  ...props
}) => {
  const [value, setValue] = useState(props.value);

  const handleChange = nextValue => {
    onChange(nextValue);
    setValue(nextValue);
  };

  return (
    <Popover className={className} position="bottom left" onFocusOutside={onFocusOutside}>
      <TextControl
        className="text-control"
        value={value.title}
        onChange={titleValue => handleChange({ title: titleValue })}
      />
      <hr />
      <LinkControl
        value={value}
        onChange={handleChange}
        forceIsEditingLink={true}
        settings={[
          {
            id: 'opensInNewTab',
            title: __('Open in New Tab', 'idg-base-theme'),
          },
          {
            id: 'makeButton',
            title: __('Make Button', 'idg-base-theme'),
          },
        ]}
      />
      <hr />
      <Button className="delete-button" onClick={onDelete}>
        {__('Remove Link', 'idg-base-theme')}
      </Button>
    </Popover>
  );
};
