/**
 * WordPress dependencies
 */
const { Spinner, Placeholder } = wp.components;

/**
 * ProductChartItemPlaceholder component. Displays a spinner while product data is being fetched.
 *
 * @return {*} JSX markup.
 */
const ProductChartItemPlaceholder = ({ showSpinner, label }) => {
  return (
    <Placeholder className="price-comparison__placeholder" instructions={label}>
      {showSpinner ? <Spinner /> : ''}
    </Placeholder>
  );
};

export default ProductChartItemPlaceholder;
