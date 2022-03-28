/**
 * WordPress dependencies.
 */
const { __, _x } = wp.i18n;
const { useEffect, useState } = wp.element;

/**
 * Internal Dependencies.
 */
const { theme_assets_directory: themeAssetsDirectory } = window?.productsSettings;

/**
 * PriceRecord props.
 *
 * @typedef PriceRecord
 *
 * @property {string} price String indicator of product's price.
 * @property {boolean} freeShipping Whether or not the product's delivery is free.
 */

/**
 * PriceRecord component. Renders single price record.
 *
 * @param {PriceRecord} props Component props.
 *
 * @return {*} JSX markup.
 */
const PriceRecord = props => {
  const { vendor, price, freeShipping, inStock } = props;
  const [vendorLogoPath, setVendorLogoPath] = useState('');

  /**
   * Returns 'Delivery' column text for the record.
   *
   * @return {string} 'Delivery' column text for the record.
   */
  const getDeliveryText = () => {
    if (freeShipping) {
      return _x('Free', 'Product delivery charges', 'idg-base-theme');
    }

    if (inStock === false) {
      return __('Out of stock', 'idg-base-theme');
    }

    return '';
  };

  /**
   * Returns absolute path for the vendor's logo. Looks for file named with
   * 'vendor-name-logo.png'.
   *
   * @return {string} Absolute path to vendor's logo file.
   */
  useEffect(() => {
    const fetchVendorLogo = async () => {
      if (!vendor) {
        return null;
      }

      const vendorName = vendor.replace(' ', '-').toLowerCase();

      let path = `${themeAssetsDirectory}/dist/static/img/${vendorName}-logo.svg`;
      const svg = await fetch(path);

      if (svg.ok) {
        setVendorLogoPath(path);
        return null;
      }

      path = `${themeAssetsDirectory}/dist/static/img/${vendorName}-logo.png`;
      const png = await fetch(path);

      if (png.ok) {
        setVendorLogoPath(path);
        return null;
      }

      setVendorLogoPath(false);
      return null;
    };

    fetchVendorLogo();
  }, []);

  const getVendorLogo = () => {
    if (!vendorLogoPath) {
      return <span>{vendor}</span>;
    }

    return <img src={vendorLogoPath} />;
  };

  return (
    <div className="price-comparison__record">
      <div className="price-comparison__image">{getVendorLogo()}</div>
      <div className="price-comparison__price">
        <span>{price || '-'}</span>
      </div>
      <div className="price-comparison__delivery">
        <span>{getDeliveryText()}</span>
      </div>
      <div className="price-comparison__view-button">
        <span>{__('View', 'idg-base-theme')}</span>
      </div>
    </div>
  );
};

export default PriceRecord;
