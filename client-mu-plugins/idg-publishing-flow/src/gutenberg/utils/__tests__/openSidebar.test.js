import { NAMESPACE } from '../../settings';

import openSidebar from '../openSidebar';

describe('openSidebar()', () => {
  it('should dispatch an action to open the sidebar if it is closed', () => {
    const spy = jest.spyOn(wp.data.select('core/edit-post'), 'getActiveGeneralSidebarName').mockImplementation(() => 'Test');
    wp.data.dispatch('core/edit-post').openGeneralSidebar = jest.fn(() => ({ type: 'TEST' }));
    openSidebar();
    expect(wp.data.dispatch('core/edit-post').openGeneralSidebar).toHaveBeenCalled();
    spy.mockRestore();
  });

  it('should dispatch an action to close the sidebar if it is open', () => {
    const sidebarName = 'test';
    const name = `${NAMESPACE}/${sidebarName}`;
    const spy = jest.spyOn(wp.data.select('core/edit-post'), 'getActiveGeneralSidebarName').mockImplementation(() => name);
    wp.data.dispatch('core/edit-post').closeGeneralSidebar = jest.fn(() => ({ type: 'TEST' }));
    openSidebar(sidebarName);
    expect(wp.data.dispatch('core/edit-post').closeGeneralSidebar).toHaveBeenCalled();
    spy.mockRestore();
  });
});
