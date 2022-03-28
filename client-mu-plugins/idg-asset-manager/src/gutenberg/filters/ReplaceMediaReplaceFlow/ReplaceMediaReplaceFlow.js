import MediaUpload from '../../components/MediaUpload';

const { createRef } = wp.element;
const { __ } = wp.i18n;
const { speak } = wp.a11y;
const { ToolbarGroup, ToolbarButton } = wp.components;
const { DOWN } = wp.keycodes;

const ReplaceMediaReplaceFlow = () => ({
  mediaId,
  allowedTypes,
  onSelect,
  name = __('Replace'),
}) => {
  const editMediaButtonRef = createRef();

  const selectMedia = media => {
    onSelect(media);
    speak(__('The media file has been replaced'));
  };

  const openOnArrowDown = event => {
    if (event.keyCode === DOWN) {
      event.preventDefault();
      event.stopPropagation();
      event.target.click();
    }
  };

  return (
    <ToolbarGroup>
      <MediaUpload
        value={mediaId}
        onSelect={media => selectMedia(media)}
        allowedTypes={allowedTypes}
        render={({ open, isOpen }) => (
          <ToolbarButton
            ref={editMediaButtonRef}
            aria-expanded={isOpen}
            aria-haspopup="true"
            onClick={open}
            onKeyDown={openOnArrowDown}
          >
            {name}
          </ToolbarButton>
        )}
      />
    </ToolbarGroup>
  );
};

export default ReplaceMediaReplaceFlow;
