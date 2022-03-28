import classnames from 'classnames';

import { I18N_DOMAIN } from '../../../settings';
import MediaUpload from '../../components/MediaUpload';

const { __ } = wp.i18n;
const { MediaUploadCheck } = wp.blockEditor;
const { Placeholder, Button } = wp.components;
const { useSelect } = wp.data;

/**
 * Wordpress Core component reference - https://github.com/WordPress/gutenberg/tree/eaf00bd30636d58c6c81884028b2d4a775954487/packages/block-editor/src/components/media-placeholder
 */
const ReplaceMediaPlaceholder = () => ({
  allowedTypes,
  className,
  icon,
  labels = {},
  mediaPreview,
  notices,
  isAppender,
  dropZoneUIOnly,
  disableMediaButtons,
  onSelectURL,
  onDoubleClick,
  children,
  ...props
}) => {
  const mediaUpload = useSelect(select => {
    const { getSettings } = select('core/block-editor');
    return getSettings().mediaUpload;
  }, []);
  const renderPlaceholder = (content, onClick) => {
    let { instructions, title } = labels;

    if (!mediaUpload && !onSelectURL) {
      instructions = __('To edit this block, you need permission to upload media.', I18N_DOMAIN);
    }

    if (instructions === undefined || title === undefined) {
      const typesAllowed = allowedTypes ?? [];

      const [firstAllowedType] = typesAllowed;
      const isOneType = typesAllowed.length === 1;
      const isAudio = isOneType && firstAllowedType === 'audio';
      const isImage = isOneType && firstAllowedType === 'image';
      const isVideo = isOneType && firstAllowedType === 'video';

      if (instructions === undefined && mediaUpload) {
        instructions = __('Upload a media file or pick one from your media library.', I18N_DOMAIN);

        if (isAudio) {
          instructions = __(
            'Upload an audio file or pick one from your media library.',
            I18N_DOMAIN,
          );
        } else if (isImage) {
          instructions = __(
            'Upload an image file or pick one from your media library.',
            I18N_DOMAIN,
          );
        } else if (isVideo) {
          instructions = __(
            'Upload a video file or pick one from your media library.',
            I18N_DOMAIN,
          );
        }
      }

      if (title === undefined) {
        title = __('Media', I18N_DOMAIN);

        if (isAudio) {
          title = __('Audio', I18N_DOMAIN);
        } else if (isImage) {
          title = __('Image', I18N_DOMAIN);
        } else if (isVideo) {
          title = __('Video', I18N_DOMAIN);
        }
      }
    }

    const placeholderClassName = classnames('block-editor-media-placeholder', className, {
      'is-appender': isAppender,
    });

    return (
      <Placeholder
        icon={icon}
        label={title}
        instructions={instructions}
        className={placeholderClassName}
        notices={notices}
        onClick={onClick}
        onDoubleClick={onDoubleClick}
        preview={mediaPreview}
      >
        {content}
        {children}
      </Placeholder>
    );
  };

  const renderMediaUploadChecked = () => (
    <>
      <MediaUpload
        allowedTypes={allowedTypes}
        isTertiary
        render={({ open }) => (
          <Button isPrimary onClick={open}>
            {__('Upload', I18N_DOMAIN)}
          </Button>
        )}
        displayUpload
        {...props}
      />
      <MediaUpload isTertiary allowedTypes={allowedTypes} {...props} />
    </>
  );

  if (dropZoneUIOnly || disableMediaButtons) {
    return null;
  }

  return <MediaUploadCheck>{renderPlaceholder(renderMediaUploadChecked())}</MediaUploadCheck>;
};

export default ReplaceMediaPlaceholder;
