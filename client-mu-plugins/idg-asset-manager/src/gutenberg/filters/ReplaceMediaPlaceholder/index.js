import ReplaceMediaPlaceholder from './ReplaceMediaPlaceholder';

const { addFilter } = wp.hooks;

/**
 * Wordpress Core component reference - https://github.com/WordPress/gutenberg/tree/eaf00bd30636d58c6c81884028b2d4a775954487/packages/block-editor/src/components/media-placeholder
 */
addFilter(
  'editor.MediaPlaceholder',
  'idg-asset-manager/replace-media-placeholder',
  ReplaceMediaPlaceholder,
);
