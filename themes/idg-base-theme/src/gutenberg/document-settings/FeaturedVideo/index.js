import FeaturedVideo from './FeaturedVideo';

const { registerPlugin } = wp.plugins;

registerPlugin('idg-base-theme-featured-video', {
  render: FeaturedVideo,
  icon: '',
});
