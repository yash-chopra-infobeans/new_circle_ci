import classnames from 'classnames';
import { isUndefined, isString } from 'lodash-es';
import Sidebar from './components/Sidebar';
import StarRating from '../review/components/StarRating';

const { Spinner } = wp.components;

const { useProduct } = window.IDGProducts;
const { useState, useEffect } = wp.element;

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

const DisplayProductWidget = ({ attributes, setAttributes }) => {
  const {
    productId,
    blockTitle,
    // productImage,
    isHalfWidth,
    isFloatRight,
    // imageFromOrigin - I don't think this is needed anymore.
  } = attributes;

  const product = useProduct(productId, {});
  const initialSetup = () => {
    let key = 0;
    if (product && !product.throughBtn && product.reviews) {
      Object.keys(product.reviews).forEach(index => {
        if (product.reviews[index].active === true) {
          key = index;
        }
        if (product.reviews[index].manual) {
          setAttributes({ activeReview: key });
        }
      });
      if (key === 0) {
        [key] = Object.keys(product.reviews);
      }
    }
    return key;
  };
  const key = initialSetup();
  const [review, updateReview] = useState(product ? product?.reviews[0] : false);
  useEffect(() => {
    updateReview(product ? product?.reviews[key] : false);
  }, [product]);
  const selectedReview = reviewId => {
    product.throughBtn = reviewId;
    setAttributes({ activeReview: reviewId });
    updateReview(product.reviews[reviewId]);
  };
  const showRRP =
    (isString(product?.geo_info?.pricing?.price) &&
      product?.geo_info?.pricing?.price?.length > 0) ||
    (isString(product?.geo_info?.pricing?.price_options) &&
      product?.geo_info?.pricing?.price_options?.length > 0);

  return (
    <>
      <Sidebar
        attributes={attributes}
        setAttributes={setAttributes}
        onSelectReview={selectedReview}
      />
      <div
        className={classnames('wp-block-product-widget-block', 'product-widget', {
          'is-half-width': isHalfWidth,
          'is-float-right': isFloatRight,
        })}
      >
        <div className="product-widget__block-title-wrapper">
          <RichText
            value={blockTitle}
            tagName="h4"
            className="product-widget__block-title"
            onChange={value => setAttributes({ blockTitle: value })}
            placeholder={__('Block title...', 'idg-base-theme')}
          />
        </div>

        {!isUndefined(product) && !product && (
          <div className="product-widget__content-wrapper">
            <div style={{ margin: '20px 10px', width: '100%', textAlign: 'center' }}>
              <Spinner />
            </div>
          </div>
        )}

        {isUndefined(product) && (
          <div className="product-widget__content-wrapper">
            <div className="product-widget__title-wrapper">
              <h3 className="product-widget__title">{__('No Product Selected')}</h3>
            </div>
          </div>
        )}

        {product && (
          <div className="product-widget__content-wrapper">
            <div className="product-widget__title-wrapper">
              <h3 className="product-widget__title">{product?.name}</h3>
            </div>
            <div className="product-widget__image-outer-wrapper">
              <div className="product-widget__image-wrapper">
                {product?.featured_media?.source_url && (
                  <img
                    className="product-widget__image"
                    src={product?.featured_media?.source_url}
                  />
                )}
              </div>
            </div>
            {review && (
              <div className="review product-widget__review-details">
                {review?.editors_choice && (
                  <div className="product-widget__review-details--editors-choice-placeholder">
                    {__("Editors' Choice")}
                  </div>
                )}

                {(review?.rating || review?.permalink) && (
                  <div className="product-widget__rating-and-review-link">
                    {review?.rating && (
                      <div className="product-widget__review-details--rating">
                        <StarRating rating={review?.rating} />
                      </div>
                    )}
                    {review?.permalink && (
                      <a href={review?.permalink} className="product-widget__review-link">
                        {__('Read our review', 'idg-base-theme')}
                      </a>
                    )}
                  </div>
                )}
              </div>
            )}
            <div className="product-widget__information">
              {showRRP && (
                <div className="product-widget__information--rrp-wrapper">
                  <span className="product-widget__information--rrp-label">
                    {`${product?.labels?.rrp_field_label || ''}: `}
                  </span>
                  <span className="product-widget__information--rrp-value">
                    {__('Preview an article to see rendered prices.', 'idg-base-theme')}
                  </span>
                </div>
              )}
              <div class="product-widget__pricing-details">
                <span class="product-widget__pricing-details--label">
                  {product?.labels?.best_prices_field_label || ''}
                  {': '}
                </span>
                <span class="product-widget__pricing-details--links-wrapper">
                  {__('Preview an article to see rendered prices.')}
                </span>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
};

export default DisplayProductWidget;
