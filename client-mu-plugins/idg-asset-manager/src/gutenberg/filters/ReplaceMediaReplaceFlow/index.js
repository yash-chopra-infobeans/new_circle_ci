/**
 * This component is used to replace the standard media replace flow, the MediaReplaceFlow is used to allow
 * various blocks that use media to have a toolbar button for replacing it.
 */

import ReplaceMediaReplaceFlow from './ReplaceMediaReplaceFlow';

const { addFilter } = wp.hooks;

/**
 * This filter is used to replace the standard media replace flow, the MediaReplaceFlow is used to allow
 * various blocks that use media to have a toolbar button for replacing it.
 *
 * Wordpress Core component reference - https://github.com/WordPress/gutenberg/tree/master/packages/block-editor/src/components/media-replace-flow
 */
addFilter('editor.MediaReplaceFlow', 'idg-asset-manager.MediaReplaceFlow', ReplaceMediaReplaceFlow);
