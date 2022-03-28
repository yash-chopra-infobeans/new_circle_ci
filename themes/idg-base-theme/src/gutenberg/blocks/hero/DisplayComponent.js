import TaxonomyControls from '../../packages/components/TaxonomyControls';
import PostTypeSelect from '../../packages/components/PostTypeSelect';
import DisplayCategories from './components/DisplayCategories';
import DisplaySelect from './components/DisplaySelect';

const { applyFilters } = wp.hooks;
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, SelectControl, RangeControl, ToggleControl } = wp.components;
const { InspectorControls } = wp.editor;

const createRange = (min, max) => num => Math.max(min, Math.min(max, num));

class DisplayComponent extends Component {
  constructor(...args) {
    super(...args);

    const { attributes } = this.props;
    this.state = {
      preview: (attributes.selectedPosts || []).length > 0,
      // Generate a key prefix as post id may not be unique.
      keyPrefix: Math.random().toString(36).substring(7),
    };

    this.range = createRange(1, 4);
  }

  /**
   * Higher order component that takes the attribute key,
   * this then returns a function which takes a value,
   * when called it updates the attribute with the key.
   * @param key
   * @returns {function(*): *}
   */
  // eslint-disable-next-line react/destructuring-assignment
  createUpdateAttribute = key => value => this.props.setAttributes({ [key]: value });

  // eslint-disable-next-line react/destructuring-assignment
  createUpdateAttributeWithFilter = (key, filter) => value =>
    this.props.setAttributes({
      [key]: filter(value),
    });

  /**
   * Toggle the preview state for the 'selection' style.
   * @returns {*}
   */
  handleTogglePreview = () =>
    this.setState(prevState => ({
      preview: !prevState.preview,
    }));

  render() {
    const { attributes, setAttributes } = this.props;
    const { preview, keyPrefix } = this.state;

    const typeOptions = applyFilters('idg-base-theme.block.list.typeOptions', [
      {
        label: __('Feed', 'idg-base-theme'),
        value: 'category',
      },
      {
        label: __('Selection', 'idg-base-theme'),
        value: 'select',
      },
    ]);

    const quantityOptions = applyFilters('idg-base-theme.block.list.quantityOptions', {
      min: 1,
      max: 4,
    });

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Hero Options', 'idg-base-theme')}>
            <SelectControl
              label={__('Hero Type', 'idg-base-theme')}
              options={typeOptions}
              value={attributes.type}
              onChange={this.createUpdateAttribute('type')}
            />
            {attributes.type === 'category' && (
              <div>
                <PostTypeSelect
                  value={attributes.postType}
                  onChange={this.createUpdateAttribute('postType')}
                />
                <RangeControl
                  label={__('Max number of posts to show:', 'idg-base-theme')}
                  min={quantityOptions.min}
                  max={quantityOptions.max}
                  value={attributes.amount || 3}
                  onChange={this.createUpdateAttributeWithFilter('amount', this.range)}
                />
                <ToggleControl
                  label={__('Exclude sponsored content', 'idg-base-theme')}
                  checked={attributes.excludeSponsored}
                  onChange={this.createUpdateAttribute('excludeSponsored')}
                />
              </div>
            )}
            {attributes.type === 'select' && (
              <button
                type="button"
                onClick={this.handleTogglePreview}
                className="components-button is-button is-default is-large"
              >
                {preview
                  ? __('Show Content Selector', 'idg-base-theme')
                  : __('Hide Content Selector', 'idg-base-theme')}
              </button>
            )}
          </PanelBody>
          {attributes.type === 'category' && (
            <PanelBody title={__('Taxonomy Options', 'idg-base-theme')} initialOpen={false}>
              <TaxonomyControls
                filters={attributes.filters}
                tag={attributes.tag}
                type={attributes.type}
                postType={attributes.postType || 'posts'}
                setAttributes={setAttributes}
              />
            </PanelBody>
          )}
          <PanelBody title={__('Display Options', 'idg-base-theme')} initialOpen={false}>
            <ToggleControl
              label={__('Display eyebrows', 'idg-base-theme')}
              checked={attributes.displayEyebrows}
              onChange={this.createUpdateAttribute('displayEyebrows')}
            />
            <ToggleControl
              label={__('Display author bylines', 'idg-base-theme')}
              checked={attributes.displayBylines}
              onChange={this.createUpdateAttribute('displayBylines')}
            />
          </PanelBody>
        </InspectorControls>
        <div className="hero">
          {attributes.type === 'category' && (
            <DisplayCategories
              amount={attributes.amount || 3}
              filters={attributes.filters}
              tag={attributes.tag}
              postType={attributes.postType}
              prefix={keyPrefix}
              displayEyebrows={attributes.displayEyebrows}
              displayBylines={attributes.displayBylines}
              excludeSponsored={attributes.excludeSponsored}
            />
          )}

          {attributes.type === 'select' && (
            <DisplaySelect
              setAttributes={setAttributes}
              selectedPosts={attributes.selectedPosts || []}
              preview={preview}
              prefix={keyPrefix}
              displayEyebrows={attributes.displayEyebrows}
              displayBylines={attributes.displayBylines}
            />
          )}
        </div>
      </Fragment>
    );
  }
}

export default DisplayComponent;
