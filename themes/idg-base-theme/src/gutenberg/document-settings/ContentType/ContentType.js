const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { CheckboxControl } = wp.components;
const { dispatch, withSelect } = wp.data;

const CONTENT_TYPE = [
  {
    label: 'Archive',
    value: 'archive',
  },
];

const ContentType = ({ meta }) => {
  const updateMeta = metaKey => value =>
    dispatch('core/editor').editPost({
      meta: {
        ...meta,
        [metaKey]: value,
      },
    });

  return (
    <PluginDocumentSettingPanel
      name="content-type"
      title={__('Content type', 'idg-base-theme')}
      className="contentType"
    >
      {CONTENT_TYPE.map(contentType => (
        <CheckboxControl
          label={contentType.label}
          checked={contentType?.value === meta?.content_type}
          onChange={() =>
            updateMeta('content_type')(
              contentType?.value === meta?.content_type ? '' : contentType.value,
            )
          }
        />
      ))}
    </PluginDocumentSettingPanel>
  );
};

export default withSelect(select => {
  const meta = select('core/editor').getEditedPostAttribute('meta');

  return {
    meta,
  };
})(ContentType);
