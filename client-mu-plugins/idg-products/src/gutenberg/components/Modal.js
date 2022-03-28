import ReactPaginate from 'react-paginate';

import usePaginatedProducts from '../hooks/usePaginatedProducts';
import Table from './Table';
import Placeholder from './Placeholder';
import Sidebar from './Sidebar';

const { __ } = wp.i18n;
const { Modal } = wp.components;
const { useState, useEffect } = wp.element;

const FILTERS = [
  {
    label: __('Filter by manufacturers'),
    taxonomy: 'manufacturer',
  },
  {
    label: __('Filter by category'),
    taxonomy: 'category',
  },
  {
    label: __('Filter by origin'),
    taxonomy: 'origin',
  },
];

const ProductModal = ({ onClose, onSelect, initialProduct = false }) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedProduct, setSelectedProduct] = useState(false);

  const [queryFilters, setQueryFilters] = useState({});

  const { products, totalPages, page, fetchProducts, isFetching } = usePaginatedProducts(
    searchQuery,
    queryFilters,
  );

  useEffect(() => {
    if (initialProduct) {
      setSelectedProduct(initialProduct);
    }
  }, []);

  return (
    <Modal
      title={__('Attach Product')}
      className="productModal"
      overlayClassName="productModal-overlay"
      onRequestClose={onClose}
      shouldCloseOnClickOutside={false}
    >
      <div className="productModal-body">
        <div className="productModal-layout">
          <div className="productModal-main">
            {(isFetching || products.length === 0) && (
              <Placeholder
                isLoading={isFetching}
                title={isFetching ? false : __('No products found.')}
              />
            )}
            {products.length > 0 && (
              <>
                <Table
                  products={products}
                  selectedProduct={selectedProduct.id}
                  onSelect={product => setSelectedProduct(product)}
                  isLoading={isFetching}
                />
                <ReactPaginate
                  previousLabel={__('Previous')}
                  nextLabel={__('Next')}
                  breakLabel={__('...')}
                  forcePage={page - 1}
                  pageCount={totalPages}
                  disabled={isFetching}
                  marginPagesDisplayed={1}
                  pageRangeDisplayed={4}
                  onPageChange={({ selected }) => fetchProducts(selected + 1)}
                  containerClassName="productModal-pagination productModal-footer productModal-footer--center"
                  activeClassName="active"
                />
              </>
            )}
          </div>
          <div className="productModal-sidebar">
            <Sidebar
              search={searchQuery}
              onSearchInput={setSearchQuery}
              disabled={isFetching}
              activeProduct={selectedProduct}
              setActiveProduct={setSelectedProduct}
              onSelect={() => onSelect(selectedProduct)}
              onFilterInput={(filter, value) => {
                setQueryFilters({
                  ...queryFilters,
                  [filter.taxonomy]: value.map(x => x.id),
                });
              }}
              filters={FILTERS}
              onCreate={product => {
                setSelectedProduct(product);
                fetchProducts(1);
              }}
            />
          </div>
        </div>
      </div>
    </Modal>
  );
};

export default ProductModal;
