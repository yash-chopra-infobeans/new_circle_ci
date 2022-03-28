import Adapter from 'enzyme-adapter-react-16';
import { configure } from 'enzyme';
import '../__mocks__/matchMedia';

configure({
  adapter: new Adapter(),
});

const ReactDOM = require('react-dom');
const components = require('@wordpress/components');
const data = require('@wordpress/data');
const element = require('@wordpress/element');
const i18n = require('@wordpress/i18n');
const url = require('@wordpress/url');
const compose = require('@wordpress/compose');
const editPost = require('@wordpress/edit-post');
const hooks = require('@wordpress/hooks');
const blocks = require('@wordpress/blocks');
const editor = require('@wordpress/editor');
const richText = require('@wordpress/rich-text');
const keycodes = require('@wordpress/keycodes');
const dom = require('@wordpress/dom');

global.userSettings = {
  uid: 1,
};

global.wp = {};
global.editorialNotes = {};

Object.defineProperty(global, 'ReactDOM', { get: () => ReactDOM });
Object.defineProperty(global.wp, 'components', { get: () => components });
Object.defineProperty(global.wp, 'data', { get: () => data });
Object.defineProperty(global.wp, 'element', { get: () => element });
Object.defineProperty(global.wp, 'i18n', { get: () => i18n });
Object.defineProperty(global.wp, 'url', { get: () => url });
Object.defineProperty(global.wp, 'compose', { get: () => compose });
Object.defineProperty(global.wp, 'editPost', { get: () => editPost });
Object.defineProperty(global.wp, 'hooks', { get: () => hooks });
Object.defineProperty(global.wp, 'blocks', { get: () => blocks });
Object.defineProperty(global.wp, 'editor', { get: () => editor });
Object.defineProperty(global.wp, 'richText', { get: () => richText });
Object.defineProperty(global.wp, 'keycodes', { get: () => keycodes });
Object.defineProperty(global.wp, 'dom', { get: () => dom });

wp.data.registerStore('idg/skeleton-plugin', {
  reducer: jest.fn(),
  selectors: jest.fn(),
});

/**
 * Shouldn't have to be done, but this gets around the
 * store returning null in tests.
 */
wp.data.registerStore('core/editor', {
  reducer: jest.fn(),
  selectors: jest.fn(),
});
