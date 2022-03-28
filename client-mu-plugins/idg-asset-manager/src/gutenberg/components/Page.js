import { I18N_DOMAIN } from '../../settings';

const { __ } = wp.i18n;
const { Slot } = wp.components;

const Page = ({ children, pageClass = '' }) => (
  <div className="wrap">
    <div className="assetManager-header">
      <h1 className="wp-heading-inline">{__('Media Management', I18N_DOMAIN)}</h1>
      <Slot name="assetManager-header" />
    </div>
    <div className={`${pageClass} assetManager-wrapper`}>
      <div className="assetManager-main">{children}</div>
    </div>
  </div>
);

export default Page;
