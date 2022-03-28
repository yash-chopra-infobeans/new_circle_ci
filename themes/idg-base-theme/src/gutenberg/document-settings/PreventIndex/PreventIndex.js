/**
 * Render file for the meta 'prevent_index'.
 */
import { isEmpty } from 'lodash-es';

const { __, sprintf } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { CheckboxControl } = wp.components;
const { dispatch, withSelect } = wp.data;
const I18N_DOMAIN = 'idg-base-theme';

const PreventIndex = ({ postType = '', meta = {}, preventIndex = 1 }) => {
  const postTypes = ['post', 'page'];

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

  const onChangePreventIndex = e => {
    updateMeta('prevent_index')(e ? preventIndex : 0);
  };

  return (
    <PluginDocumentSettingPanel
      name="prevent-index"
      title={__('Indexing', I18N_DOMAIN)}
      className="preventIndex"
    >
      <CheckboxControl
        label={__('Prevent Google Indexing', I18N_DOMAIN)}
        checked={meta?.prevent_index === preventIndex}
        onChange={e => onChangePreventIndex(e)}
        help={sprintf(
          __(
            'If checked, the %s will be prevented from indexing by Google. This MUST only be enabled by authorized users.',
            I18N_DOMAIN,
          ),
          postType,
        )}
        value={preventIndex}
      />
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
})(PreventIndex);
