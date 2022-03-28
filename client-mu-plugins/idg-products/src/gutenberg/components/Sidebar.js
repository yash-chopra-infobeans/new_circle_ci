import TermSelector from './TermSelector';
import Thumbnail from './Thumbnail';
import QuickCreate from './QuickCreate';

const { __ } = wp.i18n;
const { Panel, PanelBody, CardBody, TextControl } = wp.components;

const Sidebar = ({
  activeProduct,
  onSelect,
  onCreate,
  search,
  onSearchInput,
  onFilterInput,
  filters,
  disabled,
}) => (
  <Panel>
    {activeProduct && (
      <CardBody>
        <Thumbnail product={activeProduct} selectMethod={onSelect} isDisabled={!activeProduct} />
      </CardBody>
    )}
    <PanelBody title={__('Create new product')} initialOpen={false}>
      <QuickCreate onCreate={onCreate} />
    </PanelBody>
    <PanelBody title={__('Search existing products')} initialOpen={true}>
      <TextControl
        className="searchInput"
        value={search}
        onChange={onSearchInput}
        help={__('Type to search by product title')}
      />
      {filters.map(filter => (
        <TermSelector
          onChange={value => onFilterInput(filter, value)}
          label={filter.label}
          taxonomy={filter.taxonomy}
          disabled={disabled}
        />
      ))}
    </PanelBody>
  </Panel>
);

export default Sidebar;
