import PropTypes from 'prop-types';
import { isUndefined } from 'lodash-es';

import Sidebar from './components/Sidebar';
import Header from './components/Header';
import Footer from './components/Footer';
import PriceRecord from './components/PriceRecord';

const { useProduct } = window.IDGProducts;

const { Spinner } = wp.components;
const { __ } = wp.i18n;
const { useState } = wp.element;

const DisplayComponent = ({ attributes, setAttributes, context }) => {
  let { productId } = attributes;

  if (context['idg-base-theme/primaryProductId']) {
    productId = context['idg-base-theme/primaryProductId'];
  }

  const product = useProduct(productId, { pricing: true });

  const productPricingDetails = product?.vendor_pricing || [];

  const [isShowingAllRecords, setIsShowingAllRecords] = useState(false);

  const recordsToRender = isShowingAllRecords
    ? productPricingDetails
    : productPricingDetails.slice(0, 4);

  const noRecords = Array.isArray(productPricingDetails) && productPricingDetails.length === 0;

  /**
   * Returns placeholder's label to show helpful message.
   *
   * @return {string}
   */
  // const getPlaceholderLabel = () => {
  //   if (productId && !isUndefined(product) && !product) {
  //     return __('Fetching Product Data...', 'idg-base-theme');
  //   }

  //   if (product && noRecords) {
  //     return __('No data found for the selected product.', 'idg-base-theme');
  //   }

  //   return __(
  //     'Something went wrong while fetching product data. Make sure correct API data is provided under Settings > Global or try selecting different product in the Sidebar on right.',
  //     'idg-base-theme',
  //   );
  // };

  return (
    <>
      <Sidebar attributes={attributes} context={context} setAttributes={setAttributes} />

      <div className="wp-block-price-comparison price-comparison">
        {productId && !isUndefined(product) && !product && (
          <div style={{ margin: '20px 10px', width: '100%', textAlign: 'center' }}>
            <Spinner />
          </div>
        )}

        {!productId && (
          <div className="price-comparison__record">
            <div className="price-comparison__image">
              <span>{__('No product selected')}</span>
            </div>
          </div>
        )}

        {product && noRecords && (
          <div className="price-comparison__record">
            <div className="price-comparison__image">
              <span>{__('No data found for the selected product.')}</span>
            </div>
          </div>
        )}

        {product && recordsToRender && recordsToRender.length > 0 && (
          <>
            <Header />
            {recordsToRender.map(data => (
              <PriceRecord
                vendor={data?.vendor}
                price={data?.price}
                freeShipping={data?.freeShipping}
                inStock={data?.inStock}
              ></PriceRecord>
            ))}
            <Footer
              attributes={attributes}
              setAttributes={setAttributes}
              showViewMoreButton={productPricingDetails.length > 4}
              isShowingAllRecords={isShowingAllRecords}
              setIsShowingAllRecords={setIsShowingAllRecords}
            />
          </>
        )}
      </div>
    </>
  );
};

DisplayComponent.propTypes = {
  attributes: PropTypes.shape({
    productId: PropTypes.number.isRequired,
  }),
  setAttributes: PropTypes.func.isRequired,
};

export default DisplayComponent;
