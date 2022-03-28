import TaxonomySelect from './tax-selectors/TaxonomySelect';

const { Component } = wp.element;

class TaxonomyControls extends Component {
  constructor(...args) {
    super(...args);
    this.state = {
      taxonomies: [],
    };
  }

  componentDidMount() {
    this.fetchTaxonomies();
  }

  componentDidUpdate(prevProps) {
    const { postType } = this.props;

    const prevPostType = prevProps.postType;
    const nextPostType = postType;

    if (prevPostType !== nextPostType) {
      this.fetchTaxonomies();
    }
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

  fetchTaxonomies() {
    const { postType } = this.props;

    wp.apiRequest({
      path: `/wp/v2/taxonomies/?type=${postType === 'posts' ? 'post' : postType}`,
    }).then(results =>
      this.setState({
        taxonomies: results,
      }),
    );
  }

  render() {
    const { taxonomies } = this.state;
    const { filters, postType } = this.props;
    const { createUpdateAttribute } = this;

    const customTaxonomies = Object.keys(taxonomies).map(tax => (
      <div>
        <label>
          {taxonomies[tax].name}
          <br />
          <TaxonomySelect
            tax={taxonomies[tax]}
            route={`/idg/v1/${taxonomies[tax].slug}`}
            postType={postType}
            value={filters}
            onChange={createUpdateAttribute('filters')}
          />
          <br />
        </label>
      </div>
    ));

    return <div>{customTaxonomies}</div>;
  }
}

export default TaxonomyControls;
