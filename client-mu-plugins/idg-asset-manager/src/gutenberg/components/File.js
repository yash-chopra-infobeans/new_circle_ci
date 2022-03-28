import { isEmpty } from 'lodash';
import { I18N_DOMAIN } from '../../settings';
import Preview from './Preview';
import Form from './Form';
import DeleteButton from './DeleteButton';

const { __ } = wp.i18n;
const { Button } = wp.components;
const { useEffect, useState } = wp.element;
const File = ({
  selectedFile,
  updateItem,
  onSelect,
  onDelete,
  isSaving = false,
  unstableFeaturedImageFlow,
}) => {
  const [disableSelectAssetButton, setDisableSelectAssetButton] = useState(false);
  useEffect(() => {
    if (
      wp.data.select('core/editor').getCurrentPostType() === 'post' &&
      unstableFeaturedImageFlow &&
      selectedFile &&
      selectedFile.media_details &&
      (selectedFile.media_details.width < 1200 || selectedFile.media_details.height < 800)
    ) {
      setDisableSelectAssetButton(true);
    } else {
      setDisableSelectAssetButton(false);
    }
  });
  return (
    <div className="assetManager-uploadForm">
      <Preview {...selectedFile}>
        {updateItem && (
          <Button onClick={() => updateItem(selectedFile)} disabled={isSaving} isSecondary>
            {isSaving ? __('Saving...', I18N_DOMAIN) : __('Save', I18N_DOMAIN)}
          </Button>
        )}
        {onSelect && (
          <Button isPrimary onClick={onSelect} disabled={disableSelectAssetButton}>
            {__('Select Asset', I18N_DOMAIN)}
          </Button>
        )}
        {onDelete && (
          <DeleteButton
            deleting={selectedFile?.deleting}
            onClick={() => onDelete(selectedFile?.id)}
          >
            {!isEmpty(selectedFile?.meta?.jw_player_media_id) && (
              <p>{__('Note: this will delete the video from JW Player.', I18N_DOMAIN)}</p>
            )}
          </DeleteButton>
        )}
        {disableSelectAssetButton && (
          <p className="disabled-button-error-message">
            {__(
              'This image does not meet the minimum dimensions for a Featured Image. To use this image as featured, upload a version with minimum dimensions of 1200 x 800px.',
              I18N_DOMAIN,
            )}
          </p>
        )}
      </Preview>
      <Form />
    </div>
  );
};

export default File;
