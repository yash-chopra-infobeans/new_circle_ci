import { NAMESPACE } from '../settings';

const { select, dispatch } = wp.data;

const openEditorialNotesSidebar = (sidebarName) => {
  const name = `${NAMESPACE}/${sidebarName}`;

  if (select('core/edit-post').getActiveGeneralSidebarName() === name) {
    dispatch('core/edit-post').closeGeneralSidebar(name);
  } else {
    dispatch('core/edit-post').openGeneralSidebar(name);
  }
};

export default openEditorialNotesSidebar;
