/* eslint-disable arrow-body-style */
const { __ } = wp.i18n;
const { hooks } = wp;
const { Fragment } = wp.element;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.editor;
const { PanelBody, ToggleControl } = wp.components;

/**
 * Adds new attributes.
 */

// Defines attribute
const extraAttributes = {
  sponsored: {
    type: 'boolean',
    default: false,
  },
};

// Defines core block
const allowedBlocks = ['core/html'];

// Register attribute
const registerAttributes = (settings, name) => {
  if (!allowedBlocks.includes(name)) {
    return settings;
  }
  const blockSettings = settings;
  blockSettings.attributes = Object.assign(settings.attributes, extraAttributes);
  return blockSettings;
};
hooks.addFilter('blocks.registerBlockType', 'idg-base-theme', registerAttributes);

// Adds controls
const withInspectorControls = createHigherOrderComponent(BlockEdit => {
  return props => {
    const { setAttributes, attributes, name } = props;
    if (!allowedBlocks.includes(name)) {
      return (
        <Fragment>
          <BlockEdit {...props} />
        </Fragment>
      );
    }
    return (
      <Fragment>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody title={__('Sponsored', 'idg-base-theme')} initialOpen>
            <ToggleControl
              label={__('Sponsored embed', 'idg-base-theme')}
              checked={attributes.sponsored}
              onChange={value => {
                setAttributes({ sponsored: value });
              }}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, 'withInspectorControl');
hooks.addFilter('editor.BlockEdit', 'core/html', withInspectorControls);
