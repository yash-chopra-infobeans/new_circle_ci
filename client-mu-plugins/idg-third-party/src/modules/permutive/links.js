import { isEmpty } from 'lodash';

const links = () => {
  window.addEventListener('load', () => {
    const affiliateLinks = document.querySelectorAll('a[data-product].product-link');

    affiliateLinks.forEach(affiliateLink => {
      affiliateLink.addEventListener('click', e => {
        const productId = e.target.getAttribute('data-product');
        const productData = window?.IDG?.products?.[productId] || {};

        if (isEmpty(productData)) {
          return;
        }

        const cateogires = productData?.terms?.category || [];
        const manufacturers = productData?.terms?.manufacturer || [];
        const data = {
          category: !isEmpty(cateogires) ? cateogires.map(category => category.name).join() : '',
          name: productData?.name,
          manufacturer: !isEmpty(manufacturers)
            ? manufacturers.map(manufacturer => manufacturer.name).join()
            : '',
          // vendor: $(this).attr('data-bkvndr'),
        };

        window.permutive.track('AffiliateLinkClick', data);
      });
    });
  });
};

export default links;
