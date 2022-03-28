const { __ } = wp.i18n;
const { RangeControl, ToggleControl } = wp.components;
const { Card, CardBody } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { Selector: ProductSelector } = window.IDGProducts.components;

const Sidebar = ({ attributes, setAttributes }) => {
  if (window?.IDGPublishingFlow?.is_origin !== '1') {
    return (
      <InspectorControls>
        <p>{__('You must edit reviews in the Content Hub.')}</p>
      </InspectorControls>
    );
  }

  return (
    <InspectorControls>
      <ProductSelector
        id={attributes.primaryProductId}
        prefix={__('Primary Product')}
        title={__('Select a Primary Product')}
        disabled={attributes.comparisonProductId}
        onSelect={id => setAttributes({ primaryProductId: Number(id) })}
      />
      <ProductSelector
        id={attributes.comparisonProductId}
        prefix={__('Comparison Product')}
        title={__('Select a Comaprison Product')}
        disabled={!attributes.primaryProductId}
        onSelect={id => setAttributes({ comparisonProductId: Number(id) })}
      />
      {!attributes.comparisonProductId && (
        <Card size="small" isBorderless>
          <CardBody>
            <h3 className="block-editor-block-card__title">{__('Rating')}</h3>
            <RangeControl
              value={attributes.rating}
              step={0.5}
              help="Set to 0 to hide"
              onChange={rating => setAttributes({ rating })}
              min={0}
              max={5}
            />
            <h3 className="block-editor-block-card__title">{__('Options')}</h3>
            <ToggleControl
              label="Editors Choice"
              checked={attributes.editorsChoice}
              onChange={() => setAttributes({ editorsChoice: !attributes.editorsChoice })}
            />
          </CardBody>
        </Card>
      )}
    </InspectorControls>
  );
};

export default Sidebar;
