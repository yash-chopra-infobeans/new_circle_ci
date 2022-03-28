// import { useHistory } from 'react-router-dom';
import { isEmpty } from 'lodash-es';

import { STORE_NAME, I18N_DOMAIN } from '../../../settings';
import api from '../../api';
import { validate } from '../../utils/validateAsset';
import fileToData from '../../utils/fileToData';
import File from '../File';
import Header from '../Header';

const { __ } = wp.i18n;
const { useState } = wp.element;
const { withSelect, dispatch } = wp.data;
const { compose } = wp.compose;
const { Button } = wp.components;

const FileView = ({
  selectedFile,
  selectedFiles,
  onSelect,
  onClose,
  upload,
  multiple = false,
  goBack,
  unstableFeaturedImageFlow,
}) => {
  // const history = useHistory();
  const [saving, setSaving] = useState(false);

  const insertAsset = () => {
    const selFiles = [...selectedFiles, selectedFile];

    const selectData = selFiles.map(file => ({
      ...file,
      url: file.source_url,
    }));

    onSelect(multiple ? selectData : selectData[0]);
    onClose();
  };

  const updateAsset = file => {
    const errors = validate(file);
    const fileData = fileToData(file);

    // if there were any error's amongst any files to be uploaded, return
    if (!isEmpty(errors)) {
      dispatch(STORE_NAME).editFile({ errors }, file.id);
      return;
    }

    setSaving(true);

    api.updateFile(file.id, fileData).then(response => {
      const { data } = response;

      // replace temp store item with returened asset data
      dispatch(STORE_NAME)
        .editFile({ ...data.data }, file.id)
        .then(() => {
          setSaving(false);
        });
    });
  };

  // const deleteAsset = ID => {
  //   dispatch(STORE_NAME).editFile({ deleting: true }, ID);

  //   api.deleteFile(ID).then(() => {
  //     dispatch(STORE_NAME).deleteFile(ID);
  //     if (history) {
  //       history.push('/');
  //     }
  //   });
  // };

  return (
    <>
      <Header
        title={__('Media Management', I18N_DOMAIN)}
        isDismissible={onClose && (selectedFile || upload)}
        onClose={onClose}
      >
        <Button isSecondary onClick={goBack}>
          {__('Back', I18N_DOMAIN)}
        </Button>
      </Header>
      <File
        selectedFile={selectedFile}
        updateItem={updateAsset}
        isSaving={saving}
        onSelect={onSelect ? insertAsset : null}
        unstableFeaturedImageFlow={unstableFeaturedImageFlow}
      />
    </>
  );
};

export default compose(
  withSelect(select => ({
    selectedFile: select(STORE_NAME).getSelectedFile(),
    selectedFiles: select(STORE_NAME).getSelectedFiles(),
  })),
)(FileView);
