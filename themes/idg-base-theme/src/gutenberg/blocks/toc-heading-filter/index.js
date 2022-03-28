import classnames from 'classnames';

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorAdvancedControls } = wp.editor;
const { createHigherOrderComponent } = wp.compose;
const { ToggleControl } = wp.components;

const allowedBlocks = ['core/heading'];

function addAttributes(settings) {
  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      addedToToc: {
        type: 'boolean',
        default: false,
      },
      elementId: {
        type: 'string',
        default: '',
      },
    },
  };
}

const tocHeadingToggle = createHigherOrderComponent(BlockEdit => {
  return props => {
    const { name, attributes, setAttributes, isSelected } = props;
    const { addedToToc } = attributes;

    return (
      <Fragment>
        <BlockEdit {...props} />
        {isSelected && allowedBlocks.includes(name) && (
          <InspectorAdvancedControls>
            <ToggleControl
              label={__('Add heading to Table of Contents')}
              checked={addedToToc}
              onChange={() => setAttributes({ addedToToc: !addedToToc })}
              help={
                addedToToc
                  ? __('Displaying on Table of Contents.')
                  : __('Hidden from Table of Contents.')
              }
            />
          </InspectorAdvancedControls>
        )}
      </Fragment>
    );
  };
}, 'tocHeadingToggle');

const withElementIdSetToClientId = createHigherOrderComponent(BlockListBlock => {
  return props => {
    const {
      block: { name },
      clientId,
      setAttributes,
    } = props;

    if (name !== 'core/heading') {
      return <BlockListBlock {...props} />;
    }

    // Remove all '-' from clientId because it's not allowed in amp-state.
    let elementId = clientId.replace(/-/g, '');
    elementId = `toc${elementId}`;

    setAttributes({ elementId });

    return <BlockListBlock {...props} id={elementId} />;
  };
}, 'withElementIdSetToClientId');

function applyExtraClass(extraProps, blockType, attributes) {
  const { addedToToc } = attributes;
  if (!addedToToc && !allowedBlocks.includes(blockType.name)) {
    return extraProps;
  }
  if (addedToToc && allowedBlocks.includes(blockType.name)) {
    return {
      ...extraProps,
      className: classnames(extraProps.className, 'toc'),
    };
  }
  return null;
}

addFilter('blocks.registerBlockType', 'editorskit/custom-attributes', addAttributes);

addFilter('blocks.getSaveContent.extraProps', 'editorskit/applyExtraClass', applyExtraClass);

addFilter('editor.BlockEdit', 'editorskit/custom-advanced-control', tocHeadingToggle);

addFilter(
  'editor.BlockListBlock',
  'editorskit/with-element-id-set-to-client-id',
  withElementIdSetToClientId,
);
