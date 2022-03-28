import '../styles/embargo.scss';
import { I18N_DOMAIN } from '../settings';
import PrePublishHeader from './prepublish/PrePublishHeader';

const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { __, sprintf } = wp.i18n;

class CategoryComponent extends Component {
  isBlocked = () => {
    const { categories } = this.props;

    return categories.length <= 0;
  };

  render() {
    const { updatedStatus } = this.props;
    let title = 'published';
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = 'updated';
    }
    return (
      <>
        <PrePublishHeader.Approval name="idg-category" display={this.isBlocked} type="blocker">
          <p>{sprintf(__('An article cannot be %s without a category.', I18N_DOMAIN), title)}</p>
        </PrePublishHeader.Approval>
      </>
    );
  }
}

export default compose([
  withSelect(select => {
    const { getEditedPostAttribute } = select('core/editor');
    const categories = getEditedPostAttribute('categories');
    const updatedStatus = getEditedPostAttribute('status');

    return {
      categories,
      updatedStatus,
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
  })),
])(CategoryComponent);
