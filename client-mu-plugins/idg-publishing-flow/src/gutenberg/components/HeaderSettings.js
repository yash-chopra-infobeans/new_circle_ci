import { isEmpty } from 'lodash-es';
import { createPortal } from 'react-dom';

const { Slot, Fill } = wp.components;

function HeaderSettings({ ...props }) {
  return <Fill name="HeaderSettings" {...props} />;
}

function HeaderSettingsSlot({ scope, className, ...props }) {
  const element = document.querySelector('.edit-post-header__settings');

  return createPortal(
    <Slot name="HeaderSettings" {...props}>
      {fills => !isEmpty(fills) && <>{fills}</>}
    </Slot>,
    element,
  );
}

HeaderSettings.Slot = HeaderSettingsSlot;

export default HeaderSettings;
