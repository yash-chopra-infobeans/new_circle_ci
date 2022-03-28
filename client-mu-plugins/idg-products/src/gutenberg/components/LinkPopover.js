import Selector from './Selector';

const { __ } = wp.i18n;
const { Popover } = wp.components;
const { __experimentalLinkControl: LinkControl } = wp.blockEditor;

const LinkPopover = ({
  url = '',
  product = null,
  newTab = false,
  onChange,
  anchorRef,
  forceIsEditingLink,
  onClose,
}) => {
  return (
    <Popover focusOnMount={false} anchorRef={anchorRef} onClose={onClose} position="bottom center">
      <LinkControl
        value={{ url, title: url, opensInNewTab: newTab }}
        onChange={({ url: newUrl, opensInNewTab }) =>
          onChange({ url: newUrl, target: opensInNewTab ? '_blank' : null })
        }
        searchInputPlaceholder={__('Type url')}
        forceIsEditingLink={!url || !url.trim() || forceIsEditingLink}
        showSuggestions={false}
      />
      <Selector
        className="productLink-product"
        id={product}
        onSelect={(id, newProduct) =>
          onChange({ product: id, manufacturer: newProduct?.manufacturer.join(',') })
        }
      />
    </Popover>
  );
};

export default LinkPopover;
