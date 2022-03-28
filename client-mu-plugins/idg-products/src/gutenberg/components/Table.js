import { flatten, get } from 'lodash-es';

const { moment } = window;
const { __ } = wp.i18n;
const { decodeEntities } = wp.htmlEntities;

const listTerms = (terms, filterByTaxonomy = []) => {
  const flattenedTerms = flatten(terms);

  return flattenedTerms
    .filter(x => filterByTaxonomy.includes(x.taxonomy))
    .map(x => x.name)
    .join(', ');
};

const ProductRow = ({ data, onClick, selectedProduct }) => {
  const manufacturers = listTerms(get(data, '_embedded.wp:term'), ['manufacturer']);
  const origin = listTerms(get(data, '_embedded.wp:term'), ['origin']);
  const categories = listTerms(get(data, '_embedded.wp:term'), ['category']);

  const isSelected = data.id === selectedProduct;

  return (
    <>
      <tr
        className={`productTable-row ${isSelected ? 'productTable-row--selected' : ''}`}
        onClick={onClick}
      >
        <td colspan="2">{decodeEntities(data.title.rendered)}</td>
        <td colspan="2">{manufacturers}</td>
        <td colspan="2">{categories}</td>
        <td colspan="2">{origin}</td>
        <td colspan="2">{moment(data.date_gmt).format('DD/MM/YYYY hh:mm')}</td>
      </tr>
    </>
  );
};

const Table = ({ products, onSelect, selectedProduct, isLoading }) => {
  return (
    <table className={`productTable ${isLoading ? 'productTable--isLoading' : ''}`}>
      <thead className="productTable-header">
        <tr>
          <th colspan="2">{__('Title')}</th>
          <th colspan="2">{__('Manufacturers')}</th>
          <th colspan="2">{__('Categories')}</th>
          <th colspan="2">{__('Origin')}</th>
          <th colspan="2">{__('Publication Date')}</th>
        </tr>
      </thead>
      <tbody className="productTable-body">
        {products.map(product => (
          <ProductRow
            data={product}
            onClick={() => onSelect(product)}
            selectedProduct={selectedProduct}
          />
        ))}
      </tbody>
    </table>
  );
};

export default Table;
