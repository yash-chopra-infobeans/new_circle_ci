import { isEmpty } from 'lodash';
import { v4 as uuidv4 } from 'uuid';

const { __ } = wp.i18n;
const { Placeholder, Button } = wp.components;
const { MediaUpload, BlockControls, MediaReplaceFlow } = wp.blockEditor;
const { JWPlayer } = window.IDGAssetManager.components;
const { useState } = wp.element;

const DisplayComponent = ({ attributes, setAttributes }) => {
  const [status, setStatus] = useState('processing');
  const { domId, mediaId, id, title } = attributes;

  if (isEmpty(domId)) {
    setAttributes({ domId: uuidv4() });
  }

  const onSelect = video => {
    if (!video?.meta?.jw_player_media_id) {
      console.error('Video has no JW Player media id (meta key: jw_player_media_id).');
    }

    const newAttributes = {
      id: video.id,
      mediaId: video.meta.jw_player_media_id,
      title: video?.title,
    };

    setStatus(video?.meta?.status);
    setAttributes(newAttributes);
  };

  if (isEmpty(mediaId)) {
    return (
      <>
        <Placeholder
          icon="format-video"
          label={__('JW Player', 'idg-base-theme')}
          className="wp-block-embed"
          instructions={__(
            'Paste a link to the content you want to display on your site.',
            'idg-base-theme',
          )}
        >
          <MediaUpload
            allowedTypes={['video']}
            onSelect={onSelect}
            isTertiary
            render={({ open }) => (
              <Button isPrimary onClick={open}>
                {__('Upload', 'idg-base-theme')}
              </Button>
            )}
            displayUpload
          />
          <MediaUpload onSelect={onSelect} isTertiary allowedTypes={['video']} />
        </Placeholder>
      </>
    );
  }

  return (
    <>
      <BlockControls>
        <MediaReplaceFlow allowedTypes={['video']} onSelect={onSelect} />
      </BlockControls>
      <JWPlayer id={id} status={status} mediaId={mediaId} title={title} />
    </>
  );
};

export default DisplayComponent;
