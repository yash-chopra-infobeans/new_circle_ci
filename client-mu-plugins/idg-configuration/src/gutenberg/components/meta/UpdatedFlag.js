import { I18N_DOMAIN } from '../../settings';

const { CheckboxControl } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { __ } = wp.i18n;

const UpdatedFlagComponent = props => {
  const { isChecked, toggleUpdatedFlag } = props;

  return (
    <>
      <PluginPostStatusInfo className="article-updated-flag">
        <CheckboxControl
          label={__('Display as Updated Article', I18N_DOMAIN)}
          help={__('Will display the updated flag on the article for readers.', I18N_DOMAIN)}
          checked={isChecked}
          onChange={toggleUpdatedFlag}
        />
      </PluginPostStatusInfo>
    </>
  );
};

export default compose(
  withSelect(select => {
    const { getEditedPostAttribute } = select('core/editor');
    const { _idg_updated_flag: isChecked = false } = getEditedPostAttribute('meta');

    return {
      isChecked,
    };
  }),
  withDispatch((dispatch, ownProps) => ({
    toggleUpdatedFlag() {
      const { isChecked } = ownProps;

      dispatch('core/editor').editPost({
        meta: {
          _idg_updated_flag: !isChecked,
        },
      });
    },
  })),
)(UpdatedFlagComponent);
