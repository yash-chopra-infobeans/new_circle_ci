import { I18N_DOMAIN } from '../../settings';
import '../../styles/prepublish-panel.scss';
import Approval from './Approval';
import PrePublishModal from './PrePublishModal';

const { Button } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { PluginPrePublishPanel } = wp.editPost;
const { Component } = wp.element;
const { __ } = wp.i18n;

class PrePublishHeaderComponent extends Component {
  static contextType = Approval.contextType;

  state = {
    showConfirmation: false,
    rules: [],
    modalChildren: [],
  };

  onClick = e => {
    e.stopPropagation();

    const { setPublishStatus, savePost, closePublishSidebar } = this.props;

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
      setPublishStatus();
      savePost().then(() => {
        // @todo: create a hook from this, and use the below via the hook.
        const { getEditedPostAttribute, createNotice } = this.props;
        const publicationFlow = getEditedPostAttribute('publishing_flow');

        if (!publicationFlow?.errors) {
          return;
        }

        publicationFlow.errors.forEach(error => {
          const errorMessage = `Article could not be published: ${error}`;
          createNotice('error', errorMessage, { isDismissable: true });
        });
      });
      closePublishSidebar();
    }

    this.setState({ showConfirmation, rules, modalChildren });
  };

  onCancelConfirm = e => {
    e.stopPropagation();
    this.setState({ showConfirmation: false, rules: [] });
  };

  render() {
    const { closePublishSidebar } = this.props;
    const { showConfirmation, modalChildren } = this.state;

    return (
      <>
        {showConfirmation && (
          <PrePublishModal messages={modalChildren} onClose={this.onCancelConfirm} />
        )}
        <PluginPrePublishPanel
          className="editor-post-publish-panel__header publication-flow__prepublish"
          initialOpen
        >
          <div className="editor-post-publish-panel__header-publish-button">
            <Button
              className="editor-post-publish-button editor-post-publish-button__button"
              isPrimary
              onClick={this.onClick}
            >
              {__('Publish', I18N_DOMAIN)}
            </Button>
          </div>
          <div className="editor-post-publish-panel__header-cancel-button">
            <Button
              className="editor-post-publish-button editor-post-publish-button__button"
              isSecondary
              onClick={closePublishSidebar}
            >
              {__('Cancel', I18N_DOMAIN)}
            </Button>
          </div>
        </PluginPrePublishPanel>
      </>
    );
  }
}

const PrePublishComponent = compose([
  withSelect(select => {
    return {
      getEditedPostAttribute: select('core/editor').getEditedPostAttribute,
    };
  }),
  withDispatch(dispatch => ({
    editPost: dispatch('core/editor').editPost,
    closePublishSidebar: dispatch('core/edit-post').closePublishSidebar,
    setPublishStatus: () =>
      dispatch('core/editor').editPost(
        { status: 'publish', __publishing_flow_action: 'publish' },
        { undoIgnore: true },
      ),
    savePost: dispatch('core/editor').savePost,
    createNotice: dispatch('core/notices').createNotice,
  })),
])(PrePublishHeaderComponent);

PrePublishComponent.Approval = Approval;

export default PrePublishComponent;
