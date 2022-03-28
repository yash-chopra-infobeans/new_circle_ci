import SuppressMonetization from './SuppressMonetization';

const { registerPlugin } = wp.plugins;

wp.domReady(() => {
  registerPlugin('idg-third-party-suppression', {
    render: SuppressMonetization,
    icon: '',
  });
});
