import '../styles/embargo.scss';
import { I18N_DOMAIN } from '../settings';
import PrePublishHeader from './prepublish/PrePublishHeader';

const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { __, sprintf } = wp.i18n;

class StoryTypeComponent extends Component {
  isBlocked = () => {
    const { storyTypes } = this.props;

    return storyTypes.length <= 0;
  };

  render() {
    const { updatedStatus } = this.props;
    let title = 'published';
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = 'updated';
    }
    return (
      <>
        <PrePublishHeader.Approval name="idg-story-type" display={this.isBlocked} type="blocker">
          <p>{sprintf(__('An article cannot be %s without a story type.', I18N_DOMAIN), title)}</p>
        </PrePublishHeader.Approval>
      </>
    );
  }
}

export default compose([
  withSelect(select => {
    const { getEditedPostAttribute } = select('core/editor');
    const storyTypes = getEditedPostAttribute('story_types');
    const updatedStatus = getEditedPostAttribute('status');

    return {
      storyTypes,
      updatedStatus,
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
  })),
])(StoryTypeComponent);
