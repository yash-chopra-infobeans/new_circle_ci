import { I18N_DOMAIN } from '../../settings';
import useFieldErrors from '../hooks/useFieldErrors';

const { useEffect } = wp.element;
const { dispatch, useSelect } = wp.data;
const { Button } = wp.components;

const { entity, options } = window.CustomFields;
const { __ } = wp.i18n;

const Header = () => {
  const errors = useFieldErrors();

  const isSaving = useSelect(select =>
    select('core').isSavingEntityRecord(entity.kind, entity.name),
  );

  const hasEdits = useSelect(select =>
    select('core').hasEditsForEntityRecord(entity.kind, entity.name),
  );

  const save = () => {
    dispatch('core').saveEditedEntityRecord(entity.kind, entity.name);
  };

  useEffect(() => {
    document.body.classList.add('cf');

    document.addEventListener('keydown', event => {
      if ((event.metaKey || event.ctrlKey) && event.keyCode === 83) {
        event.preventDefault();
        save();
      }
    });
  }, []);

  return (
    <>
      <div className="cf-header">
        <h1 className="cf-title">
          {options?.title} {__('Settings', I18N_DOMAIN)}
        </h1>
        <div className="cf-header-actions">
          {errors && (
            <div className="cf-notice">
              <span>
                {__('Updating Failed.', I18N_DOMAIN)} {errors.message}
              </span>
            </div>
          )}
          <Button isPrimary disabled={!hasEdits || isSaving} isBusy={isSaving} onClick={save}>
            {errors ? __('Try again', I18N_DOMAIN) : __(hasEdits ? 'Save' : 'Saved', I18N_DOMAIN)}
          </Button>
        </div>
      </div>
    </>
  );
};

export default Header;
