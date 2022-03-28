/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;

/**
 * Header component. Renders header of the Price Comparion block.
 *
 * @return {*} JSX markup.
 */
const Header = () => {
  return (
    <div className="price-comparison__record price-comparison__record--header">
      <div>
        <span>{__('Retailer', 'idg-base-theme')}</span>
      </div>
      <div className="price-comparison__price">
        <span>{__('Price', 'idg-base-theme')}</span>
      </div>
      <div className="price-comparison__delivery">
        <span>{__('Delivery', 'idg-base-theme')}</span>
      </div>
    </div>
  );
};

export default Header;
