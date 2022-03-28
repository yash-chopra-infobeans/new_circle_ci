import Adapter from 'enzyme-adapter-react-16';
import { configure } from 'enzyme';

configure({
  adapter: new Adapter(),
});

global.wp = {};
// eslint-disable-next-line global-require, import/no-extraneous-dependencies
Object.defineProperty(global.wp, 'i18n', { get: () => require('@wordpress/i18n') });
Object.defineProperty(global.wp, 'components', { get: () => require('@wordpress/components') });
Object.defineProperty(global.wp, 'data', { get: () => require('@wordpress/data') });
Object.defineProperty(global.wp, 'compose', { get: () => require('@wordpress/compose') });
Object.defineProperty(global.wp, 'element', { get: () => require('@wordpress/element') });
Object.defineProperty(global.wp, 'hooks', { get: () => require('@wordpress/hooks') });
Object.defineProperty(global.wp, 'url', { get: () => require('@wordpress/url') });
