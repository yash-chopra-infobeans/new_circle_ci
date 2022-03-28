import { I18N_DOMAIN } from '../../settings';

const { __ } = wp.i18n;
const { Modal, Button } = wp.components;

const ConfirmBulkModal = ({ onClose, onConfirm }) => {
  return (
    <Modal
      title={__('Apply fields from first image to all images', I18N_DOMAIN)}
      className="confirmbox"
      overlayClassName="confirmbox-overlay"
      onRequestClose={onClose}
      shouldCloseOnClickOutside={false}
    >
      <div className="confirmbox-body">
        <div className="confirmbox-layout">
          <div className="confirmbox-main">
            <div>
              <p>
                {__(
                  'Are you sure you want to apply the values of Tags, Publication, Image rights, Credit, Credit URL and Notes from the first image to the other images?',
                  I18N_DOMAIN,
                )}
              </p>
              <Button className="is-secondary btn-confirm" onClick={() => onConfirm(false)}>
                {__('No', I18N_DOMAIN)}
              </Button>
              <Button className="is-primary" onClick={() => onConfirm(true)}>
                {__('Yes', I18N_DOMAIN)}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  );
};

export default ConfirmBulkModal;
