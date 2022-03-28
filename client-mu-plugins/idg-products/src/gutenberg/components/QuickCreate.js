import TermSelector from './TermSelector';
import useProductCreation from '../hooks/useProductCreation';

const { __ } = wp.i18n;
const { TextControl, Button } = wp.components;
const { useState } = wp.element;

const QuickCreate = ({ onCreate, onCancel }) => {
  const { create, isCreating } = useProductCreation();
  const [name, setName] = useState('');
  const [manufacturers, setManufacturers] = useState([]);
  const [categories, setCategories] = useState([]);

  const preventCreation = !name || manufacturers.length === 0;

  const createProduct = async () => {
    const response = await create({
      status: 'publish',
      title: name,
      manufacturer: manufacturers.map(x => x.id),
      categories: categories.map(x => x.id),
    });

    onCreate(response);
  };

  return (
    <div className="productQuickCreate">
      <TextControl label={__('Product Name')} value={name} onChange={setName} autoFocus />
      <TermSelector
        create
        value={manufacturers}
        onChange={setManufacturers}
        label={__('Manufacturers')}
        taxonomy="manufacturer"
      />
      <TermSelector
        value={categories}
        onChange={setCategories}
        label={__('Categories')}
        taxonomy="category"
      />
      <div className="productQuickCreate-controls">
        <Button
          isPrimary
          onClick={createProduct}
          isBusy={isCreating}
          disabled={preventCreation || isCreating}
        >
          {__('Create')}
        </Button>
        {onCancel && (
          <Button isTertiary onClick={onCancel} disabled={isCreating}>
            {__('Cancel')}
          </Button>
        )}
      </div>
    </div>
  );
};

export default QuickCreate;
