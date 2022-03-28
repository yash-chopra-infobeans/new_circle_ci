import Select from 'react-select';

const { Component } = wp.element;
const { __ } = wp.i18n;

class TaxonomySelect extends Component {
  constructor(...args) {
    super(...args);

    this.state = {
      loading: false,
      options: [],
    };

    this.fetch();
  }

  componentDidUpdate(prevProps) {
    const { postType } = this.props;

    const prevPostType = prevProps.postType;
    const nextPostType = postType;

    if (prevPostType !== nextPostType) {
      this.fetch();
    }
  }

  handleApiResult = results => {
    const options = results.map(result => {
      let label = result.name;
      if (result.parent) {
        label = `- ${result.name}`;
      }

      return {
        label,
        value: result.term_id,
        tax: result.taxonomy,
      };
    });

    this.setState({
      options,
      loading: false,
    });
  };

  handleInputChange = newValue => {
    const { onChange, tax } = this.props;
    let { value } = this.props;

    // If there isn't an exisiting value use newValue.
    if (!value) {
      return onChange(JSON.stringify(newValue));
    }

    value = JSON.parse(value);

    // Reset this taxonomy dropdown.
    value = value.filter(obj => obj.tax !== tax.slug);

    // Re-add this taxonomies choices if there are any.
    if (newValue) {
      Array.prototype.push.apply(value, newValue);
    }

    return onChange(JSON.stringify(value));
  };

  fetch() {
    const { route } = this.props;
    wp.apiRequest({ path: route }).then(this.handleApiResult);
  }

  render() {
    const { options, loading } = this.state;
    const { tax } = this.props;
    let { value } = this.props;

    if (value) {
      value = JSON.parse(value);
      value = value.filter(obj => obj.tax === tax.slug);
    }

    return (
      <Select
        options={options}
        styles={{
          menu: base => ({
            ...base,
            position: 'relative',
          }),
        }}
        isMulti
        isLoading={loading}
        isDisabled={loading}
        placeholder={loading ? __('Loading', 'idg-base-theme') : `Select ${tax.name}`}
        onChange={this.handleInputChange}
        isClearable
        value={value}
      />
    );
  }
}

export default TaxonomySelect;
