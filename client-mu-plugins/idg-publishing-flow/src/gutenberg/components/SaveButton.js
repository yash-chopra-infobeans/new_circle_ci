import { I18N_DOMAIN } from '../settings';
import '../styles/save-button.scss';
import Approval from './prepublish/Approval';
import PrePublishModal from './prepublish/PrePublishModal';

const { Button, createSlotFill } = wp.components;
const { withSafeTimeout, compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { __ } = wp.i18n;
const { Fill: HeaderSettings } = createSlotFill('HeaderSettings');

class SaveButtonComponent extends Component {
  static contextType = Approval.contextType;

  constructor(...args) {
    super(...args);
    this.state = {
      showConfirmation: false,
      rules: [],
      modalChildren: [],
      displaySavedMessage: false,
    };

    this.onClick = this.onClick.bind(this);
  }

  componentDidUpdate(prevProps) {
    const { isSavingPost, setTimeout } = this.props;

    if (prevProps.isSavingPost && !isSavingPost) {
      // eslint-disable-next-line react/no-did-update-set-state
      this.setState({
        displaySavedMessage: true,
      });

      setTimeout(() => {
        this.setState({
          displaySavedMessage: false,
        });
      }, 1000);
    }
  }

  onClick() {
    const { currentPostStatus, editPost, savePost } = this.props;

    const rules = [];
    const modalChildren = {
      [Approval.RequiresApprovalString]: [],
      [Approval.IsBlockedString]: [],
    };
    let showConfirmation = false;

    this.context.forEach(approvalRule => {
      if (approvalRule.display()) {
        rules.push(approvalRule.name);
        modalChildren[approvalRule.type].push(approvalRule.children);
        showConfirmation = true;
      }
    });
    if (!showConfirmation) {
      let updatedPost = {};

      if (currentPostStatus === 'publish') {
        updatedPost = {
          status: 'updated',
        };
      }

      const options = { undoIgnore: true };

      editPost(
        {
          ...updatedPost,
          __publishing_flow_action: 'save',
        },
        options,
      ).then(() => {
        savePost();
      });
    }
    this.setState({ showConfirmation, rules, modalChildren });
  }

  onCancelConfirm = e => {
    e.stopPropagation();
    this.setState({ showConfirmation: false, rules: [] });
  };

  getStatus = (status = 'draft') => {
    const { statuses } = window.IDGPublishingFlow;

    return statuses.find(item => item.name === status);
  };

  render() {
    const { isEditedPostNew, isEditedPostDirty, isAutosavingPost, isSavingPost } = this.props;
    const { displaySavedMessage } = this.state;
    const { showConfirmation, modalChildren } = this.state;

    let buttonText = __('Save', I18N_DOMAIN);

    if (displaySavedMessage || (!isEditedPostDirty && !isEditedPostNew)) {
      buttonText = __('Saved', I18N_DOMAIN);
    } else if (isAutosavingPost) {
      buttonText = __('Autosaving', I18N_DOMAIN);
    } else {
      buttonText = isSavingPost ? __('Saving', I18N_DOMAIN) : buttonText;
    }

    return (
      <div>
        {showConfirmation && (
          <PrePublishModal
            messages={modalChildren}
            calledFromSave={true}
            onClose={this.onCancelConfirm}
          />
        )}
        <HeaderSettings>
          <Button isSecondary className="custom-save-button" onClick={this.onClick}>
            {buttonText}
          </Button>
        </HeaderSettings>
      </div>
    );
  }
}

export default compose([
  withSelect(select => {
    const {
      isEditedPostNew,
      isEditedPostDirty,
      isAutosavingPost,
      isSavingPost,
      getCurrentPostAttribute,
      getEditedPostAttribute,
    } = select('core/editor');

    return {
      isEditedPostNew: isEditedPostNew(),
      isEditedPostDirty: isEditedPostDirty(),
      isAutosavingPost: isAutosavingPost(),
      isSavingPost: isSavingPost(),
      currentPostStatus: getEditedPostAttribute('status'),
      loadedPostStatus: getCurrentPostAttribute('status'),
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
    savePost: dispatch('core/editor').savePost,
  })),
  withSafeTimeout,
])(SaveButtonComponent);
