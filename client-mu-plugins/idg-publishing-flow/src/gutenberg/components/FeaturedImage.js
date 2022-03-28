import { I18N_DOMAIN } from '../settings';
import PrePublishHeader from './prepublish/PrePublishHeader';

const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { __, sprintf } = wp.i18n;

class FeaturedImageComponent extends Component {
  isBlocked = () => {
    const { featuredMedia } = this.props;

    return featuredMedia === 0;
  };

  render() {
    const { updatedStatus } = this.props;
    let title = 'published';
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = 'updated';
    }
    return (
      <>
        <PrePublishHeader.Approval
          name="idg-featured-image"
          display={this.isBlocked}
          type="blocker"
        >
          <p>
            {sprintf(__('An article cannot be %s without a featured image.', I18N_DOMAIN), title)}
          </p>
        </PrePublishHeader.Approval>
      </>
    );
  }
}

export default compose([
  withSelect(select => {
    const { getEditedPostAttribute } = select('core/editor');
    const featuredMedia = getEditedPostAttribute('featured_media');
    const updatedStatus = getEditedPostAttribute('status');

    return {
      featuredMedia,
      updatedStatus,
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
  })),
])(FeaturedImageComponent);
