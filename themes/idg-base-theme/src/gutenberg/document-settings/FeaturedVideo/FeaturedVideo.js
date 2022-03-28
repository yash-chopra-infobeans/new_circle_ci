import { isEmpty } from 'lodash-es';

import getMedia from '../../api/getMedia';

const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { MediaUploadCheck, MediaUpload } = wp.blockEditor;
const { Button, Spinner } = wp.components;
const { withSelect, dispatch } = wp.data;
const { compose } = wp.compose;
const { useEffect, useState } = wp.element;
const { dataToFile } = window.IDGAssetManager.utils;

const FeaturedVideo = ({
  postType = '',
  featuredVideoId = 0,
  featuredVideoUrl = '',
  meta = {},
}) => {
  const postTypes = ['post'];

  if (isEmpty(postType) || !postTypes.includes(postType)) {
    return null;
  }

  const [isLoading, setLoading] = useState(true);
  const [attachment, setAttachment] = useState({});

  const setVideo = media => {
    setAttachment(media);
    dispatch('core/editor').editPost({
      meta: {
        ...meta,
        featured_video_id: media.id,
      },
    });
  };

  const removeVideo = () => {
    setAttachment({});
    dispatch('core/editor').editPost({
      meta: {
        ...meta,
        featured_video_id: 0,
      },
    });
  };

  useEffect(() => {
    if (featuredVideoId === 0) {
      setLoading(false);
      return;
    }

    getMedia(featuredVideoId)
      .then(response => {
        setAttachment(response.data);
        setLoading(false);
      })
      .catch(() => {
        setLoading(false);
      });
  }, []);

  return (
    <PluginDocumentSettingPanel
      name="featured-video"
      title={__('Featured video', 'idg-base-theme')}
      className="featuredVideo"
    >
      {isLoading && <Spinner />}
      {!isLoading && (
        <MediaUploadCheck>
          <MediaUpload
            value={featuredVideoId}
            onSelect={setVideo}
            allowedTypes={['video']}
            render={({ open }) => (
              <>
                {!isEmpty(attachment) && (
                  <div
                    onClick={() =>
                      dispatch('idg/asset-manager')
                        .setFiles([dataToFile(attachment)])
                        .then(() => {
                          open(attachment.id);
                        })
                    }
                    className="jwplayer-preview"
                  >
                    <img
                      src={`https://cdn.jwplayer.com/v2/media/${attachment?.meta?.jw_player_media_id}/poster.jpg`}
                    />
                  </div>
                )}
                <div className="featuredVideo-actions">
                  <Button isPrimary onClick={open}>
                    {featuredVideoUrl
                      ? __('Replace Video', 'idg-base-theme')
                      : __('Set Video', 'idg-base-theme')}
                  </Button>
                  {featuredVideoId !== 0 && (
                    <Button onClick={removeVideo} isTertiary isDestructive>
                      {__('Remove featured video', 'idg-base-theme')}
                    </Button>
                  )}
                </div>
              </>
            )}
          />
        </MediaUploadCheck>
      )}
    </PluginDocumentSettingPanel>
  );
};

export default compose(
  withSelect(select => {
    const currentPost = select('core/editor').getCurrentPost();
    const meta = select('core/editor').getEditedPostAttribute('meta');

    return {
      postType: currentPost?.type,
      featuredVideoId: meta?.featured_video_id,
      featuredVideoUrl: meta?.featured_video_url,
      meta,
    };
  }),
)(FeaturedVideo);
