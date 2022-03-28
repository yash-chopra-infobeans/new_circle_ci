import className from 'classnames';
import { isEmpty } from 'lodash';

import { STORE_NAME, I18N_DOMAIN } from '../../../settings';
import UploadView from '../Views/UploadView';
import FileView from '../Views/FileView';
import ManagerView from '../Views/ManegerView';

const { __ } = wp.i18n;
const { Modal } = wp.components;
const { useState, useEffect } = wp.element;
const { withSelect, dispatch } = wp.data;
const { compose } = wp.compose;

const MediaModal = ({
  selectedFile,
  onClose,
  displayUpload = false,
  allowedTypes = {},
  ...props
}) => {
  const componentUnmount = () => {
    dispatch(STORE_NAME).setSelectedFiles([]);
  };

  useEffect(() => {
    return componentUnmount;
  }, []);

  const [upload, setUpload] = useState(displayUpload);

  const afterUpload = () => {
    dispatch(STORE_NAME).clearSelectedFile();
    setUpload(false);
  };

  const editFile = ID => dispatch(STORE_NAME).selectFile(ID);

  const goBack = () => dispatch(STORE_NAME).clearSelectedFile();

  const onCancel = () => setUpload(false);

  const modalContent = () => {
    let showErrMessageOnImageSelection = false;
    if (upload) {
      return (
        <UploadView onClose={onClose} afterUpload={afterUpload} onCancel={onCancel} {...props} />
      );
    }

    if (selectedFile) {
      if (
        wp.data.select('core/editor').getCurrentPostType() === 'post' &&
        props.unstableFeaturedImageFlow &&
        selectedFile &&
        selectedFile.media_details &&
        (selectedFile.media_details.width < 1200 || selectedFile.media_details.height < 800)
      ) {
        showErrMessageOnImageSelection = true;
      } else {
        return <FileView onClose={onClose} goBack={goBack} {...props} />;
      }
    }

    return (
      <ManagerView
        onClose={onClose}
        editFile={editFile}
        setUpload={() => setUpload(true)}
        additionalParams={{
          ...(!isEmpty(allowedTypes) ? { media_type: allowedTypes[0] } : {}),
        }}
        showErrMessageOnImageSelection={showErrMessageOnImageSelection}
        {...props}
      />
    );
  };

  let classes;

  if (
    wp.data.select('core/editor').getCurrentPostType() === 'post' &&
    props.unstableFeaturedImageFlow &&
    selectedFile &&
    selectedFile.media_details &&
    (selectedFile.media_details.width < 1200 || selectedFile.media_details.height < 800)
  ) {
    classes = className('assetManager-modal', {
      'assetManager-fileList': selectedFile,
    });
  } else {
    classes = className('assetManager-modal', {
      'assetManager--hasSelected': selectedFile,
      'assetManager-fileList': !selectedFile && !upload,
    });
  }

  return (
    <Modal
      title={__('Media Management', I18N_DOMAIN)}
      className={classes}
      overlayClassName="assetManager-overlay"
      onRequestClose={onClose}
      shouldCloseOnClickOutside={false}
    >
      {modalContent()}
    </Modal>
  );
};

export default compose(
  withSelect(select => ({
    selectedFile: select(STORE_NAME).getSelectedFile(),
  })),
)(MediaModal);
