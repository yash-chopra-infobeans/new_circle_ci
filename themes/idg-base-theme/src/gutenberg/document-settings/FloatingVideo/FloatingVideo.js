import { isEmpty } from 'lodash-es';

const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { ToggleControl } = wp.components;
const { withSelect, dispatch } = wp.data;
const { compose } = wp.compose;

const FloatingVideo = ({ postType = '', supressVideo = 0, meta = {} }) => {
  const postTypes = ['post', 'page'];

  if (isEmpty(postType) || !postTypes.includes(postType)) {
    return null;
  }

  const toggleSupression = () =>
    dispatch('core/editor').editPost({
      meta: {
        ...meta,
        supress_floating_video: !supressVideo,
      },
    });

  return (
    <PluginDocumentSettingPanel
      name="floating-video"
      title={__('Floating video', 'idg-base-theme')}
      className="floatingVideo"
    >
      <ToggleControl
        label={__('Suppress floating Video', 'idg-base-theme')}
        help={__(
          'If enabled the floating video will not be displayed on the page/post. This MUST only be enabled by authorized users.',
          'idg-base-theme',
        )}
        checked={supressVideo}
        onChange={toggleSupression}
      />
    </PluginDocumentSettingPanel>
  );
};

export default compose(
  withSelect(select => {
    const currentPost = select('core/editor').getCurrentPost();
    const meta = select('core/editor').getEditedPostAttribute('meta');

    return {
      postType: currentPost?.type,
      supressVideo: meta?.supress_floating_video,
      meta,
    };
  }),
)(FloatingVideo);
