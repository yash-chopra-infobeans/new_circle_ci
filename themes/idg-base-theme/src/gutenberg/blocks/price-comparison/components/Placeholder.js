/**
 * External dependencies.
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const { Spinner } = wp.components;

/**
 * PriceComparisonPlaceholder component. Displays a spinner while product data is being fetched.
 *
 * @return {*} JSX markup.
 */
const PriceComparisonPlaceholder = ({ showSpinner, label }) => {
  return (
    <div className="price-comparison__record">
      <div>
        <span>{showSpinner && <Spinner />}</span>
        <span>{label}</span>
      </div>
    </div>
  );
};

PriceComparisonPlaceholder.propTypes = {
  showSpinner: PropTypes.bool,
  label: PropTypes.string,
};

export default PriceComparisonPlaceholder;
