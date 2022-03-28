import { I18N_DOMAIN } from '../../settings';

const { __ } = wp.i18n;
const { useState } = wp.element;
const { Button, Modal } = wp.components;

const DeleteButton = ({
  onClick,
  title = __('Are you sure you want to delete this asset?', I18N_DOMAIN),
  deleting = false,
  children,
  renderButton,
}) => {
  const [isOpen, setOpen] = useState(false);

  const toggleModal = state => {
    setOpen(state || !isOpen);
  };

  return (
    <>
      {renderButton ? (
        renderButton({ onClick: toggleModal })
      ) : (
        <Button isDestructive onClick={toggleModal}>
          {__('Delete Asset', I18N_DOMAIN)}
        </Button>
      )}
      {isOpen && (
        <Modal
          className="assetManager-deleteModal"
          title={title}
          onRequestClose={() => toggleModal(false)}
        >
          {children}
          <Button isSecondary onClick={() => toggleModal(false)}>
            {__('Cancel', I18N_DOMAIN)}
          </Button>
          <Button isPrimary disabled={deleting} onClick={() => onClick({ toggleModal })}>
            {deleting ? __('Deleting...', I18N_DOMAIN) : __('Delete', I18N_DOMAIN)}
          </Button>
        </Modal>
      )}
    </>
  );
};

export default DeleteButton;
