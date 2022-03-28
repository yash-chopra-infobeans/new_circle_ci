import reducer from './reducer';
import selectors from './selectors';
import actions from './actions';
import { STORE_NAME } from '../../settings';

const { registerStore } = wp.data;

export const store = registerStore(STORE_NAME, {
  reducer,
  selectors,
  actions,
});

export default store;
