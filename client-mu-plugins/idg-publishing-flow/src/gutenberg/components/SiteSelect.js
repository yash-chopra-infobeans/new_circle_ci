import '../styles/gutenberg.scss';
import { I18N_DOMAIN } from '../settings';
import PrePublishHeader from './prepublish/PrePublishHeader';

const { SelectControl } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { __, sprintf } = wp.i18n;

class SiteSelectComponent extends Component {
  getOptions = () => {
    const { sites } = window.IDGPublishingFlow;

    const siteList = sites.map(site => ({
      ...site,
    }));

    return siteList;
  };

  isBlocked = () => {
    const { currentPost = {} } = this.props;

    return !currentPost?.isActive;
  };

  render() {
    const { currentPost, currentPostSource, updatePostSource, updatedStatus } = this.props;

    const selectedSiteName = currentPost?.label ? currentPost?.label : null;
    let title = 'publish';
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = 'update';
    }

    return (
      <>
        <PrePublishHeader.Approval name="idg-inactive-site" display={this.isBlocked} type="blocker">
          {selectedSiteName ? (
            <div>
              <p>
                <strong>{__(selectedSiteName, I18N_DOMAIN)}</strong>
                {__(' has not been activated for publishing..', I18N_DOMAIN)}
              </p>
              <p>
                {sprintf(__('Please select another publication to %s to.', I18N_DOMAIN), title)}
              </p>
            </div>
          ) : (
            <div>
              <p>{sprintf(__('Please select publication to %s to.', I18N_DOMAIN), title)}</p>
            </div>
          )}
        </PrePublishHeader.Approval>
        <div>
          <PluginPostStatusInfo className="article-status">
            <SelectControl
              label={__('Primary Publication:', I18N_DOMAIN)}
              value={currentPostSource}
              onChange={updatePostSource}
              options={this.getOptions()}
            />
          </PluginPostStatusInfo>
        </div>
      </>
    );
  }
}

export default compose(
  withSelect(select => {
    const { sites } = window.IDGPublishingFlow;
    const { getCurrentPostId } = select('core/editor');
    const publication = select('core/editor').getEditedPostAttribute('publication');
    const updatedStatus = select('core/editor').getEditedPostAttribute('status');

    let currentPost = {};
    let currentPostSource = publication[1];

    // Set the default if none set.
    if (!currentPostSource) {
      currentPostSource = '';
    } else {
      currentPost = sites.find(site => site.value === currentPostSource);
    }

    return {
      currentPost,
      currentPostSource,
      getCurrentPostId,
      updatedStatus,
    };
  }),
  withDispatch((dispatch, ownProps) => ({
    updatePostSource(postSource) {
      const { sites } = window.IDGPublishingFlow;
      const { getCurrentPostId } = ownProps;
      const sourceTerm = sites.find(site => site.value === Number(postSource));

      const selectedTerm = postSource
        ? [Number(sourceTerm?.term?.parent), Number(sourceTerm?.value)]
        : [];

      const selectedTerms = { publication: selectedTerm };

      dispatch('core/editor').editPost({
        ...selectedTerms,
      });

      dispatch('core').editEntityRecord('postType', 'post', getCurrentPostId(), selectedTerms);
    },
  })),
)(SiteSelectComponent);
