import '../gutenberg/subscriptions';
import { NAMESPACE } from '../gutenberg/settings';
import DisplayComponent from '../gutenberg/components/DisplayComponent';

import '../gutenberg/styles/gutenberg.scss';
import waitFor from '../gutenberg/utils/waitFor';

const { registerPlugin } = wp.plugins;

// Adds editorial notes plugin to editor
wp.domReady(() => {
  waitFor(
    '.edit-post-header__settings',
    () => {
      registerPlugin(NAMESPACE, {
        icon: 'editor-paragraph',
        render: DisplayComponent,
      });
    },
    true,
  );
});
