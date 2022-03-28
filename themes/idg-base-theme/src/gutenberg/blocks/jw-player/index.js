import DisplayComponent from './DisplayComponent';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const blockAttributes = {
  id: {
    // Wordpress post (attachment) id.
    type: 'integer',
  },
  mediaId: {
    // JW Player mediaId.
    type: 'string',
  },
  domId: {
    // document element id.
    type: 'string',
  },
  // Title.
  title: {
    type: 'string',
  },
};

registerBlockType('idg-base-theme/jwplayer', {
  title: __('JW Player', 'idg-base-theme'),
  keywords: [
    __('JW', 'idg-base-theme'),
    __('Player', 'idg-base-theme'),
    __('Video', 'idg-base-theme'),
  ],
  icon: 'format-video',
  category: 'embed',
  supports: {
    multiple: true,
  },
  attributes: blockAttributes,
  edit: DisplayComponent,
  // Returns null due to the component being rendered server side
  save: () => null,
});
