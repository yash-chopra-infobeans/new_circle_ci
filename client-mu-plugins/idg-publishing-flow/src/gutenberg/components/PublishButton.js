import { I18N_DOMAIN } from '../settings';
import '../styles/save-button.scss';

const { Button, createSlotFill } = wp.components;
const { withSafeTimeout, compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { __ } = wp.i18n;
const { Fill: HeaderSettings } = createSlotFill('HeaderSettings');

class PublishButtonComponent extends Component {
  constructor(...args) {
    super(...args);

    this.onClick = this.onClick.bind(this);
  }

  onClick() {
    const { editPost, savePost } = this.props;

    editPost({
      __publishing_flow_action: 'unpublish',
    }).then(() => {
      savePost();
    });
  }

  render() {
    const { shouldShowButton } = this.props;

    return (
      <>
        <HeaderSettings>
          {shouldShowButton && (
            <Button
              isPrimary
              className="editor-post-publish-button editor-post-publish-button__button publishing-flow__publish-button--unpublish"
              onClick={this.onClick}
            >
              {__('Unpublish', I18N_DOMAIN)}
            </Button>
          )}
        </HeaderSettings>
      </>
    );
  }
}

export default compose([
  withSelect(select => {
    const { getCurrentPostAttribute, getEditedPostAttribute } = select('core/editor');

    let shouldShowButton = false;

    const checkedStatus = ['publish', 'updated'];

    if (
      checkedStatus.includes(getCurrentPostAttribute('status')) &&
      !checkedStatus.includes(getEditedPostAttribute('status'))
    ) {
      shouldShowButton = true;
    }

    return {
      shouldShowButton,
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
    savePost: dispatch('core/editor').savePost,
  })),
  withSafeTimeout,
])(PublishButtonComponent);
