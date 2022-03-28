import { deburr } from 'lodash-es';

const { Component } = wp.element;
const { IconButton, TextControl, TextareaControl } = wp.components;
const { dispatch, select } = wp.data;

export const getSluggedValue = value =>
  deburr(value)
    // eslint-disable-next-line no-useless-escape
    .replace(/[\s\./]+/g, '-')
    .replace(/[^\w-]+/g, '')
    .toLowerCase();

// eslint-disable-next-line react/prefer-stateless-function
export class AdditionalSEOFields extends Component {
  constructor(props) {
    super(props);

    const { tab, getValue } = props;

    const disabled = getValue(tab.name, 'seo_slug_disabled');

    this.state = {
      disabled,
      lockIcon: disabled ? 'lock' : 'unlock',
    };
  }

  createSlugFromTitle = () => {
    const { tab, titles, handleChange } = this.props;

    const headline = titles?.seo?.value || '';
    const sluggedValue = getSluggedValue(headline);

    handleChange(tab, 'seo_slug', true)(sluggedValue);
    dispatch('core/editor').editPost({
      slug: sluggedValue,
    });
  };

  toggleLock = () => {
    const { handleChange, tab } = this.props;
    const { disabled } = this.state;

    handleChange(tab, 'seo_slug_disabled', true)(!disabled);
    this.setState({
      disabled: !disabled,
      lockIcon: !disabled ? 'lock' : 'unlock',
    });
  };

  getURLSlug = () => {
    const { tab, titles, getValue } = this.props;

    let permalink = select('core/editor').getPermalink();
    const multititleValue = getValue(tab.name, 'seo_slug');

    if (multititleValue) {
      return multititleValue;
    }

    const headline = titles?.seo?.value || '';

    try {
      const url = new URL(permalink);

      if (['http:', 'https:'].includes(url.protocol)) {
        permalink = getSluggedValue(headline);
      }
    } catch (_) {
      //
    }

    return permalink;
  };

  setURLSlug = value => {
    const { tab, handleChange } = this.props;

    const sluggedValue = getSluggedValue(value);

    handleChange(tab, 'seo_slug', true)(sluggedValue);
    dispatch('core/editor').editPost({
      slug: sluggedValue,
    });
  };

  render() {
    const { tab, getValue = '', handleChange } = this.props;

    const { disabled = false, lockIcon = 'unlock' } = this.state;

    return (
      <div className="additional-fields__container">
        <div className="slug-group">
          <TextControl
            className="group-item text-input"
            label="SEO URL Slug"
            value={this.getURLSlug()}
            disabled={disabled}
            onChange={this.setURLSlug}
          />
          <IconButton
            className="group-item"
            icon="editor-break"
            label="Create from title"
            disabled={disabled}
            onClick={this.createSlugFromTitle}
          />
          <IconButton
            className="group-item"
            icon={lockIcon}
            label="lock"
            onClick={this.toggleLock}
          />
        </div>
        <TextareaControl
          label="Description"
          value={getValue(tab.name, 'seo_desc')}
          onChange={handleChange(tab, 'seo_desc', true)}
        />
        <TextControl
          label="Canonical URL"
          value={getValue(tab.name, 'seo_canonical_url')}
          onChange={handleChange(tab, 'seo_canonical_url')}
        />
      </div>
    );
  }
}

export default AdditionalSEOFields;
