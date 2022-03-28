import useDebouncedEffect from './useDebouncedEffect';

const { useState } = wp.element;
const { addQueryArgs } = wp.url;

const PATH = '/wp/v2/product';

const DEFAULT_STATE = {
  page: 1,
  totalPages: 1,
  products: [],
  isFetching: true,
};

const DEFAULT_PARAMS = {
  order: 'desc',
  _embed: 1,
  per_page: 15,
};

const usePaginatedProducts = (search = '', queryFilters = {}) => {
  const [state, setState] = useState(DEFAULT_STATE);

  const fetchProducts = async (page = 1) => {
    setState({ ...state, isFetching: true, page });

    const response = await wp.apiFetch({
      path: addQueryArgs(PATH, {
        page,
        search,
        ...queryFilters,
        ...DEFAULT_PARAMS,
      }),
      method: 'GET',
      parse: false,
    });

    const totalPages =
      response.headers && (parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1);

    const responseData = await response.json();

    setState({
      page,
      totalPages,
      isFetching: false,
      products: responseData,
    });
  };

  useDebouncedEffect(
    () => {
      fetchProducts();
    },
    500,
    [search, queryFilters],
  );

  const { products, totalPages, isFetching, page } = state;

  return {
    page,
    products,
    totalPages,
    fetchProducts,
    isFetching,
  };
};

export default usePaginatedProducts;
