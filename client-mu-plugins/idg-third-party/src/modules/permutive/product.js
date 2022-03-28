import { isEmpty } from 'lodash-es';

const product = () => {
  const prodNames = window.IDG.getItemFromDataLayer('prodNames');
  const prodManufacturers = window.IDG.getItemFromDataLayer('prodManufacturers');
  const prodCategories = window.IDG.getItemFromDataLayer('prodCategories');
  const prodVendors = window.IDG.getItemFromDataLayer('prodVendors');

  if (
    !isEmpty(prodNames) ||
    !isEmpty(prodManufacturers) ||
    !isEmpty(prodCategories) ||
    !isEmpty(prodVendors)
  ) {
    const data = {
      names: prodNames.split(','),
      categories: prodCategories.split(','),
      manufacturers: prodManufacturers.split(','),
      vendors: prodVendors.split(','),
    };

    window.permutive.track('Product', data);
  }
};

export default product;
