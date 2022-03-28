/**
 * External dependencies.
 */
import { isEmpty, isEqual, xorWith } from 'lodash';
import className from 'classnames';

/**
 * Internal dependencies.
 */
import Sidebar from './Sidebar';

const { is_origin: isOrigin } = window.baseTheme;

/**
 * WordPress dependencies.
 */
const { InnerBlocks } = wp.blockEditor;
const { useSelect } = wp.data;
const { useEffect } = wp.element;

const ALLOWED_BLOCKS = ['idg-base-theme/product-chart-item'];

const TEMPLATE = [['idg-base-theme/product-chart-item', {}]];

/**
 * Matches contents of 2 Arrays.
 *
 * @param {Array} array1 Array 1.
 * @param {Array} array2 Array 2.
 *
 * @return {boolean}
 */
const isArrayEqual = (array1, array2) => isEmpty(xorWith(array1, array2, isEqual));

/**
 * DisplayComponent props.
 *
 * @typedef DisplayComponent
 *
 * @property {object} price String indicator of product's price.
 * @property {()=>void} setAttributes Callable function for saving attribute values.
 */

/**
 * DisplayComponent component. Renders editor view of the block.
 *
 * @param {DisplayComponent} props Component props.
 *
 * @return {*} JSX markup.
 */
const DisplayComponent = ({ attributes, setAttributes, clientId }) => {
  const { productData, isShowingRank } = attributes;

  const { innerBlocksContent } = useSelect(
    select => {
      return {
        innerBlocksContent: select('core/block-editor').getBlocksByClientId(clientId)[0] || {},
      };
    },
    [innerBlocksContent] /* eslint-disable-line no-use-before-define */,
  );

  useEffect(() => {
    const { innerBlocks } = innerBlocksContent;

    if (!Array.isArray(innerBlocks)) {
      return;
    }

    const newProductData = [];

    innerBlocks.forEach((innerBlock, index) => {
      const updatedAttributes = { ...innerBlock.attributes };

      updatedAttributes.rank = index + 1;

      newProductData.push(updatedAttributes);

      wp.data
        .dispatch('core/block-editor')
        .updateBlockAttributes(innerBlock.clientId, updatedAttributes);
    });

    if (!isArrayEqual(productData, newProductData)) {
      setAttributes({ productData: newProductData });
    }
  }, [innerBlocksContent]);

  const blockClasses = className('wp-block-product-chart product-chart', {
    'is-showing-rank': isShowingRank,
  });

  return (
    <div className={blockClasses}>
      <Sidebar attributes={attributes} setAttributes={setAttributes} />
      <InnerBlocks
        allowedBlocks={ALLOWED_BLOCKS}
        template={TEMPLATE}
        templateLock={isOrigin ? false : 'insert'}
      />
    </div>
  );
};

export default DisplayComponent;
