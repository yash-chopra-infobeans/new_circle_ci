/**
 * WordPress dependencies.
 */
const { InnerBlocks } = wp.blockEditor;

/**
 * SaveComponent component. Returns content of the block when it's getting saved from editor.
 *
 * @return {*} JSX markup.
 */
const SaveComponent = () => {
  return (
    <div className="wp-block-product-chart product-chart">
      <InnerBlocks.Content />
    </div>
  );
};

export default SaveComponent;
