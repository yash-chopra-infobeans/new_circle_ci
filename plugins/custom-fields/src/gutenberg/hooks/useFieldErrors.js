import { has } from 'lodash-es';

const { useSelect, dispatch } = wp.data;
const { entity } = window.CustomFields;

const INVALID_FIELDS_CODE = 'cf_invalid_fields';
const SAVE_POST_NOTICE_ID = 'SAVE_POST_NOTICE_ID';

const useFieldErrors = () => {
  return useSelect(select => {
    if (select('core/editor')?.didPostSaveRequestSucceed()) {
      const currentNotices = select('core/notices').getNotices();

      // Workaround for WP not removing notice on succesful save on posts.
      if (currentNotices.some(notice => notice.id === SAVE_POST_NOTICE_ID)) {
        dispatch('core/notices').removeNotice(SAVE_POST_NOTICE_ID);
      }
    }

    const error = select('core').getLastEntitySaveError(
      entity.kind,
      entity.name,
      // Will have to be addressed if we add any more entities - need to dynamically get entity id.
      // This works for now as we only have two entities: settings and post type. The settings
      // doesn't have any id and therefore works with an undefined value. First thoughts are to
      // set this in PHP, similar to the entity kind & name.
      select('core/editor')?.getCurrentPostId() || undefined,
    );

    if (error?.data && has(error.data, INVALID_FIELDS_CODE)) {
      return {
        ...error,
        data: error.data[INVALID_FIELDS_CODE],
      };
    }

    return false;
  });
};

export default useFieldErrors;
