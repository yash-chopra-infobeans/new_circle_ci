import ExternalLink from './ExternalLink';

const { registerPlugin } = wp.plugins;

/**
 * Registers 'idg-base-theme-external-post-link' plugin in the Posts Settings Sidebar.
 */
wp.domReady(() => {
  registerPlugin('idg-base-theme-external-post-link', {
    render: ExternalLink,
    icon: '',
  });
});
