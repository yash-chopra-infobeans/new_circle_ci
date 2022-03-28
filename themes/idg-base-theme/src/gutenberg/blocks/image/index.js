import Edit from './Edit';
import Settings from './Settings';
import Save from './Save';

const { addFilter } = wp.hooks;

addFilter('editor.BlockEdit', 'idg-base-theme/core-image-edit', Edit);
addFilter('blocks.registerBlockType', 'idg-base-theme/core-image-settings', Settings);
addFilter('blocks.getSaveElement', 'idg-base-theme/core-image-save', Save);
