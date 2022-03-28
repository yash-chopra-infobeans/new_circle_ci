import { isNull, isUndefined } from 'lodash-es';

import Modal from './Modal';
import Thumbnail from './Thumbnail';
import Placeholder from './Placeholder';
import useProduct from '../hooks/useProduct';

const { __ } = wp.i18n;
const { useState } = wp.element;
const { Card, CardHeader, CardBody, Button } = wp.components;

const ProductSelector = ({
  title = 'Select a Product',
  prefix = 'Product',
  id = null,
  onSelect,
  disabled = false,
  ...props
}) => {
  const [isModalOpen, toggleModal] = useState(false);
  const product = useProduct(id);

  const Controls = () => (
    <div className="productSelector-controls">
      <Button isSecondary onClick={() => toggleModal(true)}>
        {__('Replace', 'idg')}
      </Button>
      <Button isDestructive onClick={() => onSelect(null)} disabled={disabled}>
        {__('Delete', 'idg')}
      </Button>
    </div>
  );

  const ProductPreview = () => {
    if (!product || !id) {
      return (
        <>
          {isUndefined(product) && id ? (
            <>
              <p className="productSelector-warning">
                {__('No product found for id')} <strong>{id}</strong>
                {__(', has this product been deleted?')}
              </p>
              <Controls />
            </>
          ) : (
            <>
              <p className="productSelector-text">
                {__('Search product database to attach a product', 'idg')}
              </p>
              <Button onClick={() => toggleModal(true)} isPrimary disabled={disabled}>
                {__('Attach Product', 'idg')}
              </Button>
            </>
          )}
        </>
      );
    }

    return (
      <>
        <Thumbnail product={product} showExtraData={false} />
        <Controls />
      </>
    );
  };

  return (
    <div className="productSelector">
      <Card size="small" {...props}>
        <CardHeader>
          <h2 className="productSelector-title">{product ? prefix : title}</h2>
        </CardHeader>
        <CardBody>
          {isNull(product) ? <Placeholder isLoading={true} /> : <ProductPreview />}
        </CardBody>
      </Card>
      {isModalOpen && (
        <Modal
          onClose={() => toggleModal(false)}
          onSelect={x => {
            toggleModal(false);
            onSelect(x.id, x);
          }}
          initialProduct={product}
        />
      )}
    </div>
  );
};

export default ProductSelector;
