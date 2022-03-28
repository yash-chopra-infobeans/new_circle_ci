import { WINDOW_NAMESPACE } from '../settings';

import Modal from '../gutenberg/components/Modal';
import ProductReviewModal from '../gutenberg/components/ProductReviewModal';
import Thumbnail from '../gutenberg/components/Thumbnail';
import Selector from '../gutenberg/components/Selector';
import usePaginatedProducts from '../gutenberg/hooks/usePaginatedProducts';
import useProductCreation from '../gutenberg/hooks/useProductCreation';
import useProduct from '../gutenberg/hooks/useProduct';

window[WINDOW_NAMESPACE] = {};
window[WINDOW_NAMESPACE].components = {};
window[WINDOW_NAMESPACE].components.ProductReviewModal = ProductReviewModal;
window[WINDOW_NAMESPACE].components.Modal = Modal;
window[WINDOW_NAMESPACE].components.Thumbnail = Thumbnail;
window[WINDOW_NAMESPACE].components.Selector = Selector;
window[WINDOW_NAMESPACE].useProduct = useProduct;
window[WINDOW_NAMESPACE].usePaginatedProducts = usePaginatedProducts;
window[WINDOW_NAMESPACE].useProductCreation = useProductCreation;
