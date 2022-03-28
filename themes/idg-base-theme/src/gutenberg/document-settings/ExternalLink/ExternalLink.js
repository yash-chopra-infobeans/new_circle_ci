import { isEmpty } from 'lodash-es';

const { __, sprintf } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { TextControl } = wp.components;
const { dispatch, withSelect } = wp.data;

const I18N_DOMAIN = 'idg-base-theme';

const ExternalLink = ({ postType = '', meta }) => {
  const postTypes = ['post'];

  if (isEmpty(postType) || !postTypes.includes(postType)) {
    return null;
  }

  const updateMeta = metaKey => value =>
    dispatch('core/editor').editPost({
      meta: {
        ...meta,
        [metaKey]: value,
      },
    });

  let externalPostLink = meta?.external_post_link ?? '';

  const onChangeTeaser = e => {
    externalPostLink = e;
    updateMeta('external_post_link')(externalPostLink);
  };

  return (
    <PluginDocumentSettingPanel
      name="external-link"
      title={__('Redirect Link', I18N_DOMAIN)}
      className="externalPostLink"
    >
      <>
        <TextControl
          label={__('Redirect Link', I18N_DOMAIN)}
          type="text"
          onChange={e => onChangeTeaser(e)}
          value={externalPostLink}
          help={sprintf(
            __(
              'Enter any URL (including http/https), to redirect this article to that URL instead of its default permalink',
              I18N_DOMAIN,
            ),
          )}
          className="external-link"
        />
      </>
    </PluginDocumentSettingPanel>
  );
};

export default withSelect(select => {
  const meta = select('core/editor').getEditedPostAttribute('meta');
  const currentPost = select('core/editor').getCurrentPost();

  return {
    meta,
    postType: currentPost?.type,
  };
})(ExternalLink);
