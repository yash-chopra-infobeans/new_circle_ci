/**
 * Adds new tab 'Prevent Index' in the posts/page edit screen.
 */
import PreventIndex from './PreventIndex';

const { registerPlugin } = wp.plugins;

registerPlugin('idg-base-theme-prevent-index', {
  render: PreventIndex,
  icon: '',
});
