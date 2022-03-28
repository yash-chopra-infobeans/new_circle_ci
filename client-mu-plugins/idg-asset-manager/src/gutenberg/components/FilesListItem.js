import className from 'classnames';
import { isEmpty } from 'lodash-es';
import { I18N_DOMAIN } from '../../settings';
import DeleteButton from './DeleteButton';

const { __ } = wp.i18n;
const { Button, Dashicon } = wp.components;
export const FileListItem = ({
  id,
  source_url: sourceUrl,
  thumbnail,
  title = '',
  mime_type: mineType,
  uploading = false,
  progress = 100,
  errors = {},
  deleteFile,
  editFile,
  selectedFiles = [],
  deleting = false,
  multiple = false,
  meta,
  selectedId,
  setSelectedId,
  onMultipleSelect,
  value,
}) => {
  const isVideo = mineType.includes('video');
  const isImage = mineType.includes('image');

  const progressBarStyles = {
    width: `${progress}%`,
  };

  const classes = className('fileItem', {
    'fileItem--uploading': uploading,
    'fileItem--isSelected':
      (Array.isArray(value) && value.find(val => val.id === id)) || value?.id === id,
    'fileItem--errors': Object.keys(errors).length > 0,
  });

  const getThumbnailUrl = () => {
    let url = sourceUrl;

    // If thumbnail is not empty and image is not a gif use thumbnail.
    if (!isEmpty(thumbnail) && mineType !== 'image/gif') {
      url = thumbnail;
    }

    // If JW Player video use JW Player video poster image.
    if (!isEmpty(meta?.jw_player_media_id)) {
      url = `https://cdn.jwplayer.com/v2/media/${meta?.jw_player_media_id}/poster.jpg`;
    }

    return url;
  };

  const hasBeenSelected = selectedFiles.find(selFile => selFile !== undefined && selFile.id === id);

  const getIcon = () => {
    if (!isVideo) {
      return null;
    }

    return <Dashicon className="fileItem-type" icon="video-alt3" />;
  };

  return (
    <li className={[classes, selectedId === id ? 'fileItem fileItem--isSelected' : ''].join(' ')}>
      <div className="fileItem-preview">
        {getIcon()}
        {uploading && (
          <div className="fileItem-progressBar">
            <div className="progressBar-inner" style={progressBarStyles}>
              <span>{progress}</span>
            </div>
          </div>
        )}
        {!uploading && (
          <>
            <div
              className="fileItem-thumbnail"
              onClick={() => {
                setSelectedId(id);
                editFile(id);
              }}
            >
              <div className="fileItem-centered">
                {!isImage && !isVideo ? (
                  <div className="fileDocument">
                    <Dashicon className="documentIcon" icon="media-document" />
                    <span>{title}</span>
                  </div>
                ) : (
                  <img className="fileImg" src={getThumbnailUrl()} alt="" />
                )}
              </div>
            </div>
            <div className="fileItem-actions">
              {deleteFile && (
                <DeleteButton
                  deleting={deleting}
                  onClick={({ toggleModal }) => {
                    deleteFile(id).then(() => {
                      toggleModal(false);
                    });
                  }}
                  renderButton={({ onClick }) => (
                    <Button
                      className="assetManager-deleteIconBtn"
                      icon="trash"
                      label={__('Delete', I18N_DOMAIN)}
                      onClick={onClick}
                    />
                  )}
                />
              )}
              {multiple && (
                <Button
                  className={`assetManager-selectIconBtn`}
                  label={__('Add', I18N_DOMAIN)}
                  onClick={() => onMultipleSelect(id)}
                >
                  {hasBeenSelected && <Dashicon icon="yes" />}
                </Button>
              )}
            </div>
          </>
        )}
      </div>
      {!uploading && progress === 100 && (
        <div className="fileItemDetails">
          <span className="fileItemDetails-title">{title}</span>
          <span className="fileItemDetails-id">
            {__('ID: ', I18N_DOMAIN)} {id}
          </span>
        </div>
      )}
    </li>
  );
};

export default FileListItem;
