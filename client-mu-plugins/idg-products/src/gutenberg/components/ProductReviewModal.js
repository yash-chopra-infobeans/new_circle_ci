const { __ } = wp.i18n;
const { Modal, Button } = wp.components;

const ProductReviewModal = ({ onClose, onSelectSidebar, totalReviews = {} }) => {
  const copyTotalReviews = totalReviews;
  const onSelect = review => {
    Object.keys(copyTotalReviews).forEach(index => {
      copyTotalReviews[index].active = false;
    });
    copyTotalReviews[review.id].active = true;
    onSelectSidebar(review.id);
  };
  return (
    <Modal
      title={__('Select Product Review', 'idg-products')}
      className="productModal"
      overlayClassName="productModal-overlay"
      onRequestClose={onClose}
      shouldCloseOnClickOutside={false}
    >
      <div className="productModal-body">
        <div className="productModal-layout">
          <div className="productModal-main">
            {Object.keys(copyTotalReviews).length > 0 && (
              <>
                <table class="wp-list-table widefat fixed striped table-view-list posts">
                  <thead className="productTable-header">
                    <tr>
                      <th>{__('Title', 'idg-products')}</th>
                      <th>{__('Publication', 'idg-products')}</th>
                      <th>{__('Date', 'idg-products')}</th>
                      <th>{__('Action', 'idg-products')}</th>
                    </tr>
                  </thead>
                  <tbody className="productTable-body">
                    {Object.keys(copyTotalReviews).map(key => (
                      <tr>
                        <td>
                          <a target="_blank" href={copyTotalReviews[key].permalink}>
                            {copyTotalReviews[key].title}
                          </a>
                        </td>
                        <td>
                          {copyTotalReviews[key].publication &&
                          copyTotalReviews[key].publication.length > 0
                            ? copyTotalReviews[key].publication[0].name
                            : ''}
                        </td>
                        <td>{copyTotalReviews[key].formattedTime}</td>
                        <td>
                          <Button
                            className={
                              copyTotalReviews[key].active
                                ? 'is-primary product-review-select-btn'
                                : 'is-secondary product-review-select-btn'
                            }
                            onClick={() => onSelect(copyTotalReviews[key])}
                          >
                            {copyTotalReviews[key].active ? 'Selected' : 'Select'}
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </>
            )}
          </div>
        </div>
      </div>
    </Modal>
  );
};

export default ProductReviewModal;
