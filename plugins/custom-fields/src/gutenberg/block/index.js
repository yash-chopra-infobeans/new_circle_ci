import { I18N_DOMAIN, NAMESPACE } from '../../settings';
import edit from './edit';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType(`${NAMESPACE}/block`, {
  title: __('Custom Fields', I18N_DOMAIN),
  category: 'common',
  icon: 'format-aside',
  supports: {
    html: false,
    multiple: false,
    reusable: false,
    customClassName: false,
  },
  edit,
  save: () => null,
});
