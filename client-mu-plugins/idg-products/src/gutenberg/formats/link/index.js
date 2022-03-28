import LinkPopover from '../../components/LinkPopover';

const { __ } = wp.i18n;
const { RichTextToolbarButton } = wp.blockEditor;
const { useState, useMemo } = wp.element;
const { registerFormatType, applyFormat, removeFormat } = wp.richText;

const NAME = 'idg-products/link';

const createProductLinkFormat = ({ url, product, manufacturer, target }) => {
  const format = {
    type: NAME,
    attributes: {
      url: url || '',
      // product will be a number but we want a string
      product: `${product || ''}`,
      manufacturer: manufacturer || '',
    },
  };

  if (target) {
    format.attributes.target = target;
  }

  return format;
};

const Edit = ({ value, isActive, onChange, activeAttributes }) => {
  const [addingLink, setAddingLink] = useState(false);

  const anchorRef = useMemo(() => {
    const selection = window.getSelection();

    if (!selection.rangeCount) {
      return;
    }

    const range = selection.getRangeAt(0);

    if (addingLink && !isActive) {
      // eslint-disable-next-line consistent-return
      return range;
    }

    let element = range.startContainer;

    // If the caret is right before the element, select the next element.
    element = element.nextElementSibling || element;

    while (element.nodeType !== window.Node.ELEMENT_NODE) {
      element = element.parentNode;
    }

    // eslint-disable-next-line consistent-return
    return element.closest('a');
  }, [addingLink, value.start, value.end]);

  const createLinkFormat = () => {
    setAddingLink(true);
  };

  const updateLinkFormat = attributes => {
    onChange(
      applyFormat(
        value,
        createProductLinkFormat({
          ...activeAttributes,
          ...attributes,
        }),
      ),
    );
    setAddingLink(false);
  };

  const removeLinkFormat = () => {
    onChange(removeFormat(value, NAME));
  };

  return (
    <>
      <RichTextToolbarButton
        icon="products"
        title={__('Product Link')}
        onClick={!isActive ? createLinkFormat : removeLinkFormat}
        isActive={isActive}
      />
      {(addingLink || isActive) && (
        <LinkPopover
          url={activeAttributes?.url || ''}
          product={activeAttributes?.product}
          newTab={activeAttributes?.target === '_blank'}
          anchorRef={anchorRef}
          forceIsEditingLink={!activeAttributes?.url && !activeAttributes?.product && addingLink}
          onChange={updateLinkFormat}
          onClose={() => setAddingLink(false)}
        />
      )}
    </>
  );
};

registerFormatType(NAME, {
  title: __('Product Link'),
  tagName: 'a',
  className: 'product-link',
  attributes: {
    url: 'href',
    product: 'data-product',
    manufacturer: 'data-manufacturer',
    target: 'target',
  },
  edit: Edit,
});
