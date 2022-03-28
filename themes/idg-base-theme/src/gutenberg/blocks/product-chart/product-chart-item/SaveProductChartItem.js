/**
 * WordPress dependencies.
 */
const { InnerBlocks } = wp.blockEditor;

/**
 * SaveProductChartItem component. Returns content of the block when it's getting saved from editor.
 *
 * @return {*} JSX markup.
 */
const SaveProductChartItem = () => {
  return (
    <div className="wp-inner-block-product-chart inner-product-chart">
      <InnerBlocks.Content />
    </div>
  );
};

export default SaveProductChartItem;
