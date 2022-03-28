import FloatingVideo from './FloatingVideo';

const { registerPlugin } = wp.plugins;

registerPlugin('idg-base-theme-floating-video', {
  render: FloatingVideo,
  icon: '',
});
