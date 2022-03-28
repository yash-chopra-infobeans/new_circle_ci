const { Component } = wp.element;
const { __ } = wp.i18n;
const { SelectControl } = wp.components;

class PostTypeSelect extends Component {
  constructor(...args) {
    super(...args);

    this.state = {
      loading: false,
      options: [],
      route: '/wp/v2/types',
    };
    this.fetch();
  }

  handleApiResult = results => {
    const types = results;
    delete types.attachment;
    delete types.wp_block;
    delete types.sidebar;
    delete types.page;
    delete types.product;
    delete types.sponsored_link;
    // Jetpack Post Types
    delete types.feedback;
    delete types.jp_pay_product;
    delete types.jp_pay_order;

    const options = Object.keys(types).map(key => ({
      label: types[key].name,
      value: types[key].slug,
    }));

    this.setState({
      options,
      loading: false,
    });
  };

  handleInputChange = value => {
    const { onChange } = this.props;

    return onChange(value);
  };

  fetch() {
    const { route } = this.state;
    wp.apiRequest({ path: route }).then(this.handleApiResult);
  }

  render() {
    const { options, loading } = this.state;
    const { value } = this.props;

    return (
      <SelectControl
        label={__('Content Type', 'idg-base-theme')}
        options={options}
        isLoading={loading}
        isDisabled={loading}
        onChange={this.handleInputChange}
        value={value}
      />
    );
  }
}

export default PostTypeSelect;
