const { is_origin: isOrigin } = window.baseTheme;

const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { ToggleControl, PanelBody, Button } = wp.components;
// const { MediaUpload, MediaUploadCheck } = wp.editor;

// const ALLOWED_MEDIA_TYPES = ['image'];

const { Selector: ProductSelector, ProductReviewModal } = window.IDGProducts.components;
const { useState } = wp.element;
const Sidebar = ({ attributes, setAttributes, onSelectReview }) => {
  if (!isOrigin) {
    return null;
  }

  const { productId, isHalfWidth, isFloatRight, linksInNewTab, version } = attributes;

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

  // const instructions = (
  //   <p>{__('To upload the image, you need permission to upload media.', 'idg-base-theme')}</p>
  // );

  // const ProductMediaUpload = () => {
  //   return (
  //     <MediaUploadCheck fallback={instructions}>
  //       <MediaUpload
  //         onSelect={image => setAttributes({ productImage: image.source_url })}
  //         allowedTypes={ALLOWED_MEDIA_TYPES}
  //         value={productImage}
  //         render={({ open }) => (
  //           <Button className={'editor-product-image__toggle'} onClick={open} isSecondary>
  //             {(productImage === 0
  //               ? __('Set', 'idg-base-theme')
  //               : __('Replace', 'idg-base-theme')) + __(' custom product image', 'idg-base-theme')}
  //           </Button>
  //         )}
  //       />
  //     </MediaUploadCheck>
  //   );
  // };

  return (
    <InspectorControls>
      <div className="product-widget-block-inspector">
        <ProductSelector
          id={productId}
          prefix={__('Selected product: ', 'idg-base-theme')}
          title={__('Select a product', 'idg-base-theme')}
          onSelect={id => setAttributes({ productId: Number(id) })}
        />

        <PanelBody title={__('Product data settings', 'idg-base-theme')}>
          <ToggleControl
            label={__('Open product links in new tab', 'idg-base-theme')}
            checked={linksInNewTab}
            onChange={() => setAttributes({ linksInNewTab: !linksInNewTab })}
          />

          <>
            {/* <ProductMediaUpload />
            {productImage !== 0 && (
              <Button
                className="image-reset-btn"
                onClick={() => setAttributes({ productImage: 0 })}
                isDestructive
              >
                {__('Reset custom product image', 'idg-base-theme')}
              </Button>
            )} */}

            <ToggleControl
              label={__('Half width', 'idg-base-theme')}
              className="half-widget-wrapper"
              checked={isHalfWidth}
              onChange={() => setAttributes({ isHalfWidth: !isHalfWidth })}
            />

            {isHalfWidth && (
              <ToggleControl
                label={__('Float right', 'idg-base-theme')}
                checked={isFloatRight}
                onChange={() => setAttributes({ isFloatRight: !isFloatRight })}
              />
            )}
            {version === '1.1.0' && Object.keys(reviews).length > 0 && (
              <div class="select-review-btn">
                <Button className="is-secondary" onClick={() => toggleModal(true)}>
                  {__('Select Review', 'idg-base-theme')}
                </Button>
              </div>
            )}
            {version === '1.1.0' && isModalOpen && Object.keys(reviews).length > 0 && (
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
          </>
        </PanelBody>
      </div>
    </InspectorControls>
  );
};

export default Sidebar;
