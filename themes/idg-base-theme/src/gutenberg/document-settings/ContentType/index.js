import ContentType from './ContentType';

const { registerPlugin } = wp.plugins;

wp.domReady(() => {
  registerPlugin('idg-base-theme-content-type', {
    render: ContentType,
    icon: '',
  });
});
