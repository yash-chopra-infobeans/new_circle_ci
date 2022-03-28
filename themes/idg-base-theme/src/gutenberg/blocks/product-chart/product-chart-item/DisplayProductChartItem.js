import { isString, isUndefined } from 'lodash-es';
import Sidebar from './components/Sidebar';
import Placeholder from './components/Placeholder';
import StarRating from '../../review/components/StarRating';

const { __ } = wp.i18n;
const { RichText, InnerBlocks } = wp.blockEditor;
const { useProduct } = window.IDGProducts;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;

/**
 * Matches contents of 2 Arrays.
 *
 * @param {Array} array1 Array 1.
 * @param {Array} array2 Array 2.
 *
 * @return {boolean}
 */
const DisplayProductChartItem = ({ attributes, setAttributes, clientId }) => {
  const {
    rank,
    productId,
    productTitle,
    productContent,
    titleOverride,
    productRating,
    ratingOverride,
    productImageSize,
    productImage,
    version,
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
  const [review, updateReview] = useState(product ? product?.reviews[key] : false);
  const showRRP =
    (isString(product?.geo_info?.pricing?.price) &&
      product?.geo_info?.pricing?.price?.length > 0) ||
    (isString(product?.geo_info?.pricing?.price_options) &&
      product?.geo_info?.pricing?.price_options?.length > 0);
  const showEditorsChoice = review?.editors_choice && review?.type === 'primary';
  const title = titleOverride ? productTitle : product?.name || '';
  const fallbackRating = review?.type === 'primary' ? review.rating : false;
  const rating = ratingOverride ? productRating : fallbackRating;
  useEffect(() => {
    updateReview(product ? product?.reviews[key] : false);
  }, [product]);

  const selectedReview = reviewId => {
    product.throughBtn = reviewId;
    setAttributes({ activeReview: reviewId });
    updateReview(product.reviews[reviewId]);
  };
  const ALLOWED_BLOCKS = [
    'core/image',
    'core/paragraph',
    'core/html',
    'core/heading',
    'core/list',
    'core/embed',
    'idg-base-theme/jwplayer',
  ];
  const getPlaceholderLabel = () => {
    if (productId && !isUndefined(product) && !product) {
      return '';
    }

    if (!productId && !product) {
      return __('No Product Selected');
    }

    return __(
      'Something went wrong while fetching product data. Make sure correct API data is provided under Settings > Global or try selecting different product.',
      'idg-base-theme',
    );
  };

  const { innerBlocksContent } = useSelect(
    select => {
      return {
        innerBlocksContent: select('core/block-editor').getBlocksByClientId(clientId)[0] || {},
      };
    },
    [innerBlocksContent] /* eslint-disable-line no-use-before-define */,
  );

  const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();
  const allowedEmbedBlocks = ['youtube'];
  if (
    selectedBlock &&
    (selectedBlock.attributes.parent === 'idg-base-theme/product-chart-item' ||
      selectedBlock.name === 'idg-base-theme/product-chart-item')
  ) {
    wp.blocks.getBlockVariations('core/embed').forEach(function (blockVariation) {
      if (allowedEmbedBlocks.indexOf(blockVariation.name) === -1) {
        wp.blocks.unregisterBlockVariation('core/embed', blockVariation.name);
      }
    });
  }

  let blockImg = '';
  if (productImage) {
    blockImg = productImage;
  } else {
    blockImg = product?.featured_media?.source_url;
  }

  useEffect(() => {
    if (!Array.isArray(innerBlocksContent.innerBlocks)) {
      return;
    }

    innerBlocksContent.innerBlocks.forEach(innerBlock => {
      const attrs = { ...innerBlock.attributes };
      attrs.parent = 'idg-base-theme/product-chart-item';
      wp.data.dispatch('core/block-editor').updateBlockAttributes(innerBlock.clientId, attrs);
    });

    const updatedAttributes = { ...innerBlocksContent.attributes };
    wp.data
      .dispatch('core/block-editor')
      .updateBlockAttributes(innerBlocksContent.clientId, updatedAttributes);

    setAttributes({ productContentInner: innerBlocksContent.innerBlocks });
  }, [innerBlocksContent]);

  return (
    <>
      {
        <Sidebar
          attributes={attributes}
          setAttributes={setAttributes}
          clientId={clientId}
          rating={rating}
          product={product}
          productImage={productImage}
          productImageSize={productImageSize}
          onSelectReview={selectedReview}
        />
      }

      <div className="product-chart-separator"></div>
      <div className="wp-block-product-chart-item product-chart-item">
        {!product && (
          <Placeholder
            showSpinner={productId && !isUndefined(product)}
            label={getPlaceholderLabel()}
          />
        )}
        {product && (
          <>
            <div className="product-chart-item__title-wrapper">
              {rank !== 0 && (
                <h3 className="product-chart-item__title-wrapper--rank">{`${rank}. `}</h3>
              )}
              <RichText
                value={title}
                tagName="h3"
                className="product-chart-item__title-wrapper--title"
                onChange={val => setAttributes({ productTitle: val, titleOverride: true })}
                placeholder={__('Product title...', 'idg-base-theme')}
              />
            </div>
            <div
              className={`product-chart-item__image-outer-wrapper product-chart-item__image-outer-wrapper--${productImageSize.toLowerCase()}`}
            >
              {blockImg && (
                <div className="product-chart-item__image-wrapper">
                  <img className="product-chart-item__image" src={blockImg} />
                </div>
              )}
            </div>
            {(showEditorsChoice || rating) && (
              <div className="review product-chart-item__review-details">
                {showEditorsChoice && (
                  <div className="product-chart__review-details--editors-choice-placeholder">
                    {__("Editors' Choice")}
                  </div>
                )}
                {rating && (
                  <div className="product-chart-item__review-details--rating">
                    <StarRating rating={rating} />
                  </div>
                )}
              </div>
            )}
            <div className="product-chart-item__information">
              {showRRP && (
                <div className="product-chart-item__information--rrp-wrapper">
                  <span className="product-chart-item__information--rrp-label">
                    {`${product?.labels?.rrp_field_label || ''}: `}
                  </span>
                  <span className="product-chart-item__information--rrp-value">
                    {__('Preview an article to see rendered prices.', 'idg-base-theme')}
                  </span>
                </div>
              )}
              <div className="product-chart-item__pricing-details">
                <span className="product-chart-item__pricing-details--label">
                  {`${product?.labels?.best_prices_field_label || ''}: `}
                </span>
                <span className="product-chart-item__pricing-details--links-wrapper">
                  {__('Preview an article to see rendered prices.')}
                </span>
              </div>
            </div>
            {(version === '1.0.0' || productContent) && (
              <RichText
                value={productContent}
                tagName="p"
                className="product-chart-item__description"
                onChange={val => setAttributes({ productContent: val })}
                placeholder={__('Product description...', 'idg-base-theme')}
              />
            )}
            {version === '1.1.0' && !productContent && (
              <InnerBlocks allowedBlocks={ALLOWED_BLOCKS} />
            )}
            {review?.permalink && (
              <>
                <span>{__(`Read our full `, 'idg-base-theme')}</span>
                <a href={review?.permalink} className="product-chart-item__review-link">
                  {__(`${product?.name} review`, 'idg-base-theme')}
                </a>
              </>
            )}
          </>
        )}
      </div>
    </>
  );
};

export default DisplayProductChartItem;
