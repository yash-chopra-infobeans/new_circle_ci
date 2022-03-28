import { isObject } from 'lodash-es';

const { useState, useEffect } = wp.element;
const { addQueryArgs } = wp.url;
const DEFAULT_PARAMS = {
  _embed: 1,
};

const useProduct = (id, transform = false) => {
  const [product, setProduct] = useState(null);
  const path = isObject(transform) ? '/idg/v1' : '/wp/v2';

  const fetchProduct = async () => {
    setProduct(null);

    try {
      const articleId = wp.data.select('core/editor').getCurrentPostId();
      DEFAULT_PARAMS.article_id = articleId;
      const article = { article_id: articleId };
      let copiedTransform = transform;
      if (isObject(transform)) {
        copiedTransform = { ...transform, ...article };
      }
      // transform.article_id = articleId;
      const response = await wp.apiFetch({
        path: addQueryArgs(
          `${path}/product/${id}`,
          isObject(transform) ? copiedTransform : DEFAULT_PARAMS,
        ),
        method: 'GET',
      });
      setProduct(response);
    } catch (e) {
      setProduct(undefined);
    }
  };

  useEffect(() => {
    if (!id) {
      setProduct(undefined);
      return;
    }

    fetchProduct();
  }, [id]);

  return product;
};

export default useProduct;
