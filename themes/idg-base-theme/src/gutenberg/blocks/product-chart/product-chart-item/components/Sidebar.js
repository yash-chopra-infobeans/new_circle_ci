const { is_origin: isOrigin } = window.baseTheme;
const { __ } = wp.i18n;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, RangeControl, ButtonGroup, Button } = wp.components;

const ALLOWED_MEDIA_TYPES = ['image'];

const { Selector: ProductSelector, ProductReviewModal } = window.IDGProducts.components;
const { useState } = wp.element;
const IMAGE_SIZES = {
  Small: 'medium',
  Medium: 'medium',
  Large: 'medium_large',
};

const Sidebar = ({ attributes, setAttributes, rating, onSelectReview }) => {
  if (!isOrigin) {
    return null;
  }
  const { productId, titleOverride, ratingOverride, productImageSize, productImage } = attributes;
  const { useProduct } = window.IDGProducts;
  const product = useProduct(productId, {});
  const [isModalOpen, toggleModal] = useState(false);
  let reviews = {};
  if (product && product.reviews) {
    reviews = product.reviews;
    Object.keys(reviews).forEach(index => {
      if (reviews[index].status !== 'publish') {
        delete reviews[index];
      }
    });
  }

  const instructions = (
    <p>{__('To upload the image, you need permission to upload media.', 'idg-base-theme')}</p>
  );

  const ProductMediaUpload = () => {
    return (
      <MediaUploadCheck fallback={instructions}>
        <MediaUpload
          onSelect={image => setAttributes({ productImage: image.url })}
          allowedTypes={ALLOWED_MEDIA_TYPES}
          value={productImage}
          render={({ open }) => (
            <Button className="editor-product-image__toggle" onClick={open} isSecondary>
              {(productImage === ''
                ? __('Set', 'idg-base-theme')
                : __('Replace', 'idg-base-theme')) + __(' custom product image', 'idg-base-theme')}
            </Button>
          )}
        />
      </MediaUploadCheck>
    );
  };

  return (
    <InspectorControls>
      {isOrigin && (
        <ProductSelector
          id={productId}
          prefix={__('Selected product: ', 'idg-base-theme')}
          title={__('Select a product', 'idg-base-theme')}
          onSelect={id => setAttributes({ productId: Number(id) })}
        />
      )}

      <PanelBody
        className="product-chart-item-product-data-settings"
        title={__('Product Chart Item Settings', 'idg-base-theme')}
      >
        <>
          <ProductMediaUpload />
          {productImage !== '' && (
            <Button
              className="image-reset-btn"
              onClick={() => setAttributes({ productImage: '' })}
              isDestructive
            >
              {__('Reset custom product image', 'idg-base-theme')}
            </Button>
          )}
        </>

        <div className="image-size-selector-wrapper">
          <h3 className="components-base-control__label block-editor-block-card__title">
            {__('Select product image size', 'idg-base-theme')}
          </h3>
          <ButtonGroup>
            {Object.keys(IMAGE_SIZES).map((size, index) => (
              <Button
                key={`img-btn-${index}`}
                onClick={() => setAttributes({ productImageSize: size })}
                isPrimary={size === productImageSize}
                isSecdonary={size !== productImageSize}
              >
                {size}
              </Button>
            ))}
          </ButtonGroup>
        </div>
        <RangeControl
          label={__('Rating', 'idg-base-theme')}
          value={rating}
          step={0.5}
          help={__('Set to 0 to hide', 'idg-base-theme')}
          onChange={val => setAttributes({ productRating: val, ratingOverride: true })}
          min={0}
          max={5}
        />
        {titleOverride && (
          <Button
            className="title-reset-btn"
            onClick={() => setAttributes({ productTitle: '', titleOverride: false })}
            isDestructive
          >
            {__('Reset title', 'idg-base-theme')}
          </Button>
        )}
        {ratingOverride && (
          <Button
            className="rating-reset-btn"
            onClick={() => setAttributes({ productRating: '', ratingOverride: false })}
            isDestructive
          >
            {__('Reset rating', 'idg-base-theme')}
          </Button>
        )}
        {Object.keys(reviews).length > 0 && (
          <div class="select-review-btn">
            <Button className="is-secondary" onClick={() => toggleModal(true)}>
              {__('Select Review', 'idg-base-theme')}
            </Button>
          </div>
        )}
        {isModalOpen && Object.keys(reviews).length > 0 && (
          <ProductReviewModal
            className="productModal"
            onClose={() => toggleModal(false)}
            onSelectSidebar={review => {
              toggleModal(false);
              onSelectReview(review);
            }}
            totalReviews={reviews}
          />
        )}
      </PanelBody>
    </InspectorControls>
  );
};

export default Sidebar;
