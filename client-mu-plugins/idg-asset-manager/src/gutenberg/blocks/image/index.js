import { get, isEmpty } from 'lodash-es';

import { I18N_DOMAIN } from '../../../settings';

const { __ } = wp.i18n;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;
const { addFilter } = wp.hooks;
const { useSelect } = wp.data;

const imageBlocks = ['core/image'];

const getLargestRatioSize = (sizes, ratio) => {
  if (isEmpty(sizes)) {
    return undefined;
  }

  return Object.keys(sizes)
    .filter(k => k.endsWith(`-r${ratio}`))
    .sort((a, b) => {
      const aProc = parseInt(a.replace(`-r${ratio}`, ''), 10);
      const bProc = parseInt(b.replace(`-r${ratio}`, ''), 10);
      if (aProc < bProc) {
        return 1;
      }
      if (aProc > bProc) {
        return -1;
      }
      return 0;
    })
    .shift();
};

const extendImageBlock = createHigherOrderComponent(BlockEdit => {
  return props => {
    if (!imageBlocks.includes(props.name)) {
      return <BlockEdit {...props} />;
    }

    const { attributes, setAttributes, isSelected } = props;
    const { id, sizeSlug } = attributes;

    const image = useSelect(
      select => {
        const { getMedia } = select('core');
        return id && isSelected ? getMedia(id) : null;
      },
      [id, isSelected],
    );

    const updateImage = newSizeSlug => {
      const newSize = get(image, ['media_details', 'sizes', newSizeSlug]);

      if (!newSize || !newSize?.source_url) {
        return null;
      }

      return setAttributes({
        url: newSize.source_url,
        width: newSize?.width || undefined,
        height: newSize?.height || undefined,
        sizeSlug: newSizeSlug,
      });
    };

    const imageRatios = [
      {
        label: __('Original', I18N_DOMAIN),
        value: 'full',
      },
    ];

    if (image) {
      const sizes = get(image, ['media_details', 'sizes']);

      const r16by9 = getLargestRatioSize(sizes, '16:9');
      const r3by2 = getLargestRatioSize(sizes, '3:2');
      const r1by1 = getLargestRatioSize(sizes, '1:1');

      if (r16by9 !== undefined) {
        imageRatios.push({
          label: __('16:9', I18N_DOMAIN),
          value: r16by9,
        });
      }

      if (r3by2 !== undefined) {
        imageRatios.push({
          label: __('3:2', I18N_DOMAIN),
          value: r3by2,
        });
      }

      if (r1by1 !== undefined) {
        imageRatios.push({
          label: __('1:1', I18N_DOMAIN),
          value: r1by1,
        });
      }
    }

    return (
      <Fragment>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody title={__('Image Ratio')} initialOpen={true}>
            <SelectControl
              label={__('Image Ratio', I18N_DOMAIN)}
              value={sizeSlug}
              options={imageRatios}
              onChange={updateImage}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, 'extendImageBlock');

addFilter('editor.BlockEdit', 'idg-asset-manager/extend-image-block', extendImageBlock);
