import '../../styles/prepublish-panel.scss';
import Approval from './Approval';
import PrePublishModal from './PrePublishModal';

const { Component } = wp.element;
const { select } = wp.data;

class PreUpdateHeaderComponent extends Component {
  static contextType = Approval.contextType;

  state = {
    showConfirmation: false,
    rules: [],
    modalChildren: [],
  };

  validateUpdate = e => {
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

    this.setState({ showConfirmation, rules, modalChildren });

    if (showConfirmation) {
      e.stopPropagation();
    }
  };

  onCancelConfirm = e => {
    e.stopPropagation();
    this.setState({ showConfirmation: false, rules: [] });
  };

  render() {
    const publishButton = document.querySelector('button.editor-post-publish-button__button');
    publishButton.removeEventListener('click', this.validateUpdate);
    const { showConfirmation, modalChildren } = this.state;
    const updatedStatus = select('core/editor').getEditedPostAttribute('status');
    const checkedStatus = ['publish', 'updated'];
    if (checkedStatus.includes(updatedStatus)) {
      publishButton.addEventListener('click', this.validateUpdate, false);
    }

    return (
      <>
        {showConfirmation && (
          <PrePublishModal messages={modalChildren} onClose={this.onCancelConfirm} />
        )}
      </>
    );
  }
}

export default PreUpdateHeaderComponent;
