import { NAMESPACE } from '../gutenberg/settings';
import DisplayComponent from '../gutenberg/components/DisplayComponent';

import '../gutenberg/plugins/multi-title';

const { registerPlugin } = wp.plugins;
const { unregisterBlockType } = wp.blocks;

wp.domReady(() => {
  // Adds editorial notes plugin to editor
  registerPlugin(NAMESPACE, {
    icon: 'editor-paragraph',
    render: DisplayComponent,
  });

  // remove core/video block from editor
  unregisterBlockType('core/video');
});
