import PropTypes from 'prop-types';
import { I18N_DOMAIN } from '../../settings';
import Approval from './Approval';

const { Modal } = wp.components;
const { Component } = wp.element;
const { __, sprintf, _n } = wp.i18n;
const { select } = wp.data;

const maybePluralize = (count, noun, suffix = 's') => `${noun}${count !== 1 ? suffix : ''}`;

class PrePublishModal extends Component {
  getTitle() {
    const updatedStatus = select('core/editor').getEditedPostAttribute('status');
    const { messages, calledFromSave } = this.props;
    let title = __('Publishing Blocked', I18N_DOMAIN);
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = __('Updating Blocked', I18N_DOMAIN);
    }
    if (calledFromSave) {
      title = __('Saving Blocked', I18N_DOMAIN);
    }

    if (messages[Approval.IsBlockedString].length > 0) {
      return title;
    }

    return __('Confirm Publish', I18N_DOMAIN);
  }

  getMessages() {
    const { messages } = this.props;

    if (messages[Approval.IsBlockedString].length > 0) {
      return messages[Approval.IsBlockedString];
    }

    return messages[Approval.RequiresApprovalString];
  }

  render() {
    const { onClose } = this.props;
    const updatedStatus = select('core/editor').getEditedPostAttribute('status');
    let title = 'published';
    if (updatedStatus === 'updated' || updatedStatus === 'publish') {
      title = 'updated';
    }

    const messages = this.getMessages().map(message => (
      <div className="publication-flow__prepublish--message">{message}</div>
    ));

    return (
      <Modal title={this.getTitle()} onRequestClose={onClose}>
        <div className="publication-flow__prepublish--notice">
          {_n('There is ', 'There are ', messages.length, I18N_DOMAIN)}
          <strong>
            {messages.length} {maybePluralize(messages.length, __('issue', I18N_DOMAIN))}
          </strong>
          {sprintf(__(' found preventing the article being %s. ', I18N_DOMAIN), title)}
        </div>
        {messages}
      </Modal>
    );
  }
}

PrePublishModal.propTypes = {
  onClose: PropTypes.func.isRequired,
  messages: PropTypes.array.isRequired,
};

export default PrePublishModal;
