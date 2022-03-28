import ReplaceMediaUpload from './ReplaceMediaUpload';

const { addFilter } = wp.hooks;

/**
 * This filter is used to replace the standard media upload modal.
 *
 * Wordpress core component reference - https://github.com/WordPress/gutenberg/tree/master/packages/block-editor/src/components/media-upload
 */
addFilter('editor.MediaUpload', 'idg-asset-manager.MediaUpload', ReplaceMediaUpload);
