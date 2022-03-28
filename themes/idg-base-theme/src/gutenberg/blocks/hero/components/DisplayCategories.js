/* eslint-disable no-underscore-dangle */
import isString from 'lodash-es/isString';
import PostItem from './display/PostItem';
import PostItemAlt from './display/PostItemAlt';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { dateI18n, __experimentalGetSettings } = wp.date;

const queryFields = [
  'id',
  'date',
  'type',
  'title',
  'excerpt',
  'eyebrow.eyebrow',
  'eyebrow.eyebrow_style',
  'review_score',
  'author',
  'meta.multi_title',
  'featured_media',
  'categories',
  '_links.wp:featuredmedia',
  '_links.wp:term',
  '_links.author',
  '_embedded',
].join(',');

const queryEmbed = ['wp:featuredmedia', 'wp:term', 'author'].join(',');

class DisplayCategories extends Component {
  constructor(...args) {
    super(...args);
    this.state = {
      results: [],
      loading: false,
    };
  }

  componentDidMount() {
    const { filters } = this.props;

    if (filters) {
      this.fetchPostsByTaxonomy();
    } else {
      this.fetchPosts();
    }
  }

  componentDidUpdate(prevProps) {
    const { filters, postType, excludeSponsored } = this.props;

    const prevFilters = this.normaliseTaxonomy(prevProps.filters);
    const nextFilters = this.normaliseTaxonomy(filters);

    const prevPostType = prevProps.postType;
    const nextPostType = postType;

    const prevEclude = prevProps.excludeSponsored;
    const nextEclude = excludeSponsored;

    if (
      prevFilters.length !== nextFilters.length ||
      prevPostType !== nextPostType ||
      prevEclude !== nextEclude
    ) {
      this.fetchPostsByTaxonomy();
    }
  }

  alterResults = response =>
    response.map(resp => {
      let tags = false;

      if (resp._embedded['wp:term']) {
        tags = resp._embedded['wp:term']
          .reduce((prev, curr) => [...prev, ...curr], [])
          .filter(tag => tag.taxonomy === 'category');
      }

      let featuredImage = false;

      if (resp.featured_media || resp.featured_media > 0) {
        featuredImage =
          resp._embedded['wp:featuredmedia'][0].media_details?.sizes?.['300-r3:2']?.source_url ||
          resp._embedded['wp:featuredmedia'][0].media_details?.source_url ||
          false;
      }

      let author = false;

      if (resp._embedded.author || resp._embedded.author > 0) {
        author = resp._embedded.author[0].name;
      }

      let excerpt = false;

      if (resp.excerpt) {
        excerpt = this.strip(resp.excerpt.rendered);
        excerpt = excerpt.length > 250 ? `${excerpt.slice(0, 250)}...` : excerpt;
      }

      let eyebrow = false;
      let eyebrowStyle = false;

      if (resp.eyebrow) {
        eyebrow = resp.eyebrow.eyebrow;
        eyebrowStyle = resp.eyebrow.eyebrow_style;
      }

      const dateFormat = __experimentalGetSettings().formats.date;
      const publishedDate = dateI18n(dateFormat, resp.date);

      return {
        id: resp.id,
        title: resp.title.rendered,
        link: resp.link,
        tags,
        excerpt,
        featuredImage,
        date: publishedDate,
        author,
        type: resp.type,
        eyebrow,
        eyebrowStyle,
      };
    });

  strip = html => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    return doc.body.textContent || '';
  };

  normaliseTaxonomy = (taxonomy = '[]') => {
    let normal = taxonomy;

    if (isString(normal)) {
      normal = JSON.parse(normal);
    }

    if (!Array.isArray(normal)) {
      normal = [normal];
    }

    normal = normal.map(val => {
      if (isString(val)) {
        return JSON.parse(val);
      }

      return val;
    });

    return normal.filter(Boolean);
  };

  /**
   * Creates the taxonomy filter path.
   * @param {object} Objects of taxonomy types with arrays of taxonomies.
   * @returns {string} wp api path that includes taxonomy filters.
   */
  createPath(filterListGrouped) {
    const { postType, excludeSponsored } = this.props;
    let path = '';
    let restBase = '';

    if (postType === 'post') {
      path = `/wp/v2/${postType}s/`;
    } else {
      path = `/wp/v2/${postType}/`;
    }

    let i = 0;
    let symbol = '';

    Object.keys(filterListGrouped).map(tax => {
      if (i === 0) {
        symbol = '?';
      } else {
        symbol = '&';
      }

      if (tax === 'category') {
        restBase = 'categories';
      } else if (tax === 'post_tag') {
        restBase = 'tags';
      } else {
        restBase = tax;
      }

      const finalFilterList = filterListGrouped[tax].map(v => v.value).join(',');
      path = path.concat(`${symbol}${restBase}=${finalFilterList}`);

      i += 1;
      return path;
    });

    path = path.concat(`&per_page=20&status=publish&_fields=${queryFields}&_embed=${queryEmbed}`);

    if (excludeSponsored) {
      path = path.concat('&exclude_sponsored=true');
    }

    return path;
  }

  /**
   * Gets all posts of the current chosen Post Type,
   * and filters by chosen taxonomies.
   * Then sets them to the state.
   * @returns {function(*): *}
   */
  fetchPostsByTaxonomy() {
    const { filters } = this.props;

    const filterList = this.normaliseTaxonomy(filters);

    if (!filterList.length) {
      this.fetchPosts();
      return;
    }

    this.setState({
      loading: true,
    });

    const groupBy = function group(xs, key) {
      return xs.reduce((rv, x) => {
        // eslint-disable-next-line no-param-reassign
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
      }, {});
    };

    const filterListGrouped = groupBy(filterList, 'tax');

    const path = this.createPath(filterListGrouped);

    wp.apiRequest({
      path,
    }).then(results =>
      this.setState({
        results: this.alterResults(results),
        loading: false,
      }),
    );
  }

  /**
   * Gets all posts of the current chosen Post Type,
   * then sets them to the state.
   * @returns {function(*): *}
   */
  fetchPosts() {
    const { postType, excludeSponsored } = this.props;

    let path = `/wp/v2/${postType}/?per_page=20&status=updated,publish&_fields=${queryFields}&_embed=${queryEmbed}`;

    if (postType === 'post') {
      path = `/wp/v2/${postType}s/?per_page=20&status=updated,publish&_fields=${queryFields}&_embed=${queryEmbed}`;
    }

    if (excludeSponsored) {
      path = path.concat('&exclude_sponsored=true');
    }

    this.setState({
      loading: true,
    });

    wp.apiRequest({
      path,
    }).then(results => {
      this.setState({
        results: this.alterResults(results),
        loading: false,
      });
    });
  }

  render() {
    const { prefix, amount, displayEyebrows, displayBylines } = this.props;
    const { loading, results } = this.state;

    const hasResults = results.length > 0;

    if (loading) {
      return (
        <div>
          <p>{__('Loading...', 'idg-base-theme')}</p>
        </div>
      );
    }

    if (!hasResults) {
      return (
        <div>
          <p className="linklist-container">{__('No Items found', 'idg-base-theme')}</p>
        </div>
      );
    }

    return (
      <div>
        {amount === 4 || amount === 3 ? (
          <div className={`hero-inner hero-${amount}`}>
            <div className="hero-col hero-col-1">
              {results
                .filter((item, i) => i < amount)
                .slice(0, 1)
                .map(result => (
                  <PostItem
                    key={`${prefix}-${result.id}`}
                    {...result}
                    displayEyebrows={displayEyebrows}
                    displayBylines={displayBylines}
                  />
                ))}
            </div>
            <div className={`hero-col hero-col-${amount - 1}`}>
              {amount === 4
                ? results
                    .filter((item, i) => i < amount)
                    .slice(1, amount)
                    .map(result => (
                      <PostItemAlt
                        key={`${prefix}-${result.id}`}
                        {...result}
                        displayEyebrows={displayEyebrows}
                        displayBylines={displayBylines}
                      />
                    ))
                : results
                    .filter((item, i) => i < amount)
                    .slice(1, amount)
                    .map(result => (
                      <PostItem
                        key={`${prefix}-${result.id}`}
                        {...result}
                        displayEyebrows={displayEyebrows}
                        displayBylines={displayBylines}
                      />
                    ))}
            </div>
          </div>
        ) : (
          <div className={`hero-inner hero-${amount}`}>
            {results
              .filter((item, i) => i < amount)
              .map(result => (
                <PostItem
                  key={`${prefix}-${result.id}`}
                  {...result}
                  displayEyebrows={displayEyebrows}
                  displayBylines={displayBylines}
                />
              ))}
          </div>
        )}
      </div>
    );
  }
}

export default DisplayCategories;
