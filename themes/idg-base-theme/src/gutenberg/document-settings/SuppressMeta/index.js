import SuppressMeta from './SuppressMeta';

const { registerPlugin } = wp.plugins;

registerPlugin('idg-base-theme-suppress-meta', {
  render: SuppressMeta,
  icon: '',
});
