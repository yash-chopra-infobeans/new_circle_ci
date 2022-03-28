import classnames from 'classnames';
import TitleContent from './TitleContent';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { Button, PanelBody, SelectControl, TextControl } = wp.components;
const { RichText } = wp.blockEditor;
const { applyFilters } = wp.hooks;
const { InspectorControls } = wp.editor;

class DisplayComponent extends Component {
  constructor(...args) {
    super(...args);

    this.addItem = this.addItem.bind(this);
  }

  /**
   * Higher order component that takes the attribute key,
   * this then returns a function which takes a value,
   * when called it updates the attribute with the key.
   * @param key
   * @returns {function(*): *}
   */
  createUpdateAttribute = key => value => this.props.setAttributes({ [key]: value });

  updateItem(index, value) {
    const { attributes, setAttributes } = this.props;

    setAttributes({
      items: attributes.items.map((item, i) => (i === index ? { ...item, ...value } : item)),
    });
  }

  addItem() {
    const {
      attributes: { items = [] },
      setAttributes,
    } = this.props;

    setAttributes({
      items: [
        ...items,
        {
          card_content_image: '',
          card_content_eyebrow: '',
          card_content_title: '',
          card_content_text: '',
          card_content_url: '',
        },
      ],
    });
  }

  setActiveIndex(index = null) {
    this.setState({
      activeIndex: index,
    });
  }

  deleteItem(index) {
    const { attributes, setAttributes } = this.props;
    const reducedArr = [...attributes.items];
    reducedArr.splice(index, 1);
    setAttributes({ items: reducedArr });
  }

  render() {
    const { attributes } = this.props;
    const items = attributes.items || [];
    const { ctaTitle, ctaStyle } = attributes;
    const ctaLinkAttr = ctaStyle === 'cta-link-style';
    const ctaLinkStyle = ctaLinkAttr ? 'cta-link--style' : 'cta-link-button';
    const length = 20; // Max char length
    const ctaTitleTrimmed = ctaTitle.substring(0, length);

    const listItems = items.map((item, index) => (
      <TitleContent
        value={item}
        onChange={this.updateItem.bind(this, index)}
        onClick={this.setActiveIndex.bind(this, index)}
        onDelete={() => this.deleteItem(index)}
      />
    ));

    const blockTitle = (
      <RichText
        className="block-title"
        multiline={() => false}
        value={attributes.blockTitle}
        placeholder={__('Add block Title', 'idg-base-theme')}
        onChange={this.createUpdateAttribute('blockTitle')}
      />
    );

    const ctaButton = (
      <div className="cta-button-wrapper">
        <Button className="cta-button" href={attributes.ctaLink}>
          {ctaTitle !== '' ? (
            ctaTitleTrimmed
          ) : (
            <span className="default-cta-link">Add CTA Text</span>
          )}
        </Button>
      </div>
    );

    const classes = classnames('card-block', {
      'card-block--style-block': attributes.blockStyle === 'block-title-style',
    });

    const styleOptions = applyFilters('idg.card.block.styleOptions', [
      {
        label: __('Default Style', 'idg-base-theme'),
        value: 'style-default',
      },
      {
        label: __('Block Header', 'idg-base-theme'),
        value: 'block-title-style',
      },
    ]);

    const ctaStyleOptions = applyFilters('idg.card.block.ctaStyleOptions', [
      {
        label: __(' CTA default Style', 'idg-base-theme'),
        value: 'cta-default',
      },
      {
        label: __('CTA Link', 'idg-base-theme'),
        value: 'cta-link-style',
      },
    ]);

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Card Style', 'idg-base-theme')}>
            <SelectControl
              label={__('Card Style', 'idg-base-theme')}
              options={styleOptions}
              value={attributes.blockStyle}
              onChange={this.createUpdateAttribute('blockStyle')}
            />
          </PanelBody>
          <PanelBody title={__('CTA Options', 'idg-base-theme')}>
            <TextControl
              label={__('CTA Link', 'idg-base-theme')}
              value={attributes.ctaLink}
              onChange={this.createUpdateAttribute('ctaLink')}
              help={__(
                'Internal: "/page-name", External: "https://wwww.domain.com/page-name"',
                'idg-base-theme',
              )}
            />
            <TextControl
              label={__('CTA Text', 'idg-base-theme')}
              placeholder={__('Add CTA text', 'idg-base-theme')}
              value={ctaTitle}
              onChange={this.createUpdateAttribute('ctaTitle')}
            />
            <SelectControl
              label={__('CTA Style', 'idg-base-theme')}
              options={ctaStyleOptions}
              value={attributes.ctaStyle}
              onChange={this.createUpdateAttribute('ctaStyle')}
            />
          </PanelBody>
        </InspectorControls>
        <div className={classes}>
          <div className="card-block-title">{blockTitle}</div>
          <div className="card-items">{listItems}</div>
          <div className={ctaLinkStyle}>{ctaButton}</div>
          <Button className="add-card-btn" onClick={this.addItem}>
            <span className="dashicons dashicons-plus-alt2" aria-label="Add item"></span>
          </Button>
        </div>
      </Fragment>
    );
  }
}

export default DisplayComponent;
