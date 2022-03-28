import { isNull } from 'lodash';

const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { CheckboxControl } = wp.components;
const { useEntityProp } = wp.coreData;
const { useSelect } = wp.data;

const META_KEY = 'suppress_monetization';

const INTEGRATIONS = [
  {
    label: __('Page Ads', 'idg-third-party'),
    vendor: 'page_ads',
  },
  {
    label: __('Content Ads', 'idg-third-party'),
    vendor: 'content_ads',
  },
  {
    label: __('JW Player Ads', 'idg-third-party'),
    vendor: 'jwplayer',
  },
  {
    label: __('Nativo', 'idg-third-party'),
    vendor: 'nativo',
  },
  {
    label: __('Outbrain', 'idg-third-party'),
    vendor: 'outbrain',
  },
];

const SuppressMonetization = () => {
  const postType = useSelect(select => {
    return select('core/editor').getCurrentPostType();
  }, []);

  if (!['post', 'page'].includes(postType)) {
    return null;
  }

  const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

  const parsedMeta = meta[META_KEY] ? JSON.parse(meta[META_KEY]) : false;

  const toggle = vendor => {
    if (parsedMeta[vendor]) {
      parsedMeta[vendor] = false;
    } else {
      parsedMeta[vendor] = true;
    }
    setMeta({
      ...meta,
      [META_KEY]: JSON.stringify(parsedMeta),
    });
  };

  const isChecked = vendor => {
    let parsedVal = false;
    if (parsedMeta && !isNull(parsedMeta)) {
      parsedVal = parsedMeta[vendor];
    }
    return parsedVal;
  };

  return (
    <PluginDocumentSettingPanel
      name="suppress-monetization"
      title={__('Suppress Monetization', 'idg-third-party')}
    >
      {INTEGRATIONS.map(({ label, vendor }) => (
        <CheckboxControl
          label={label}
          checked={isChecked(vendor)}
          onChange={() => toggle(vendor)}
        />
      ))}
      <span>{__('These MUST only be enabled by authorized users.', 'idg-third-party')}</span>
    </PluginDocumentSettingPanel>
  );
};

export default SuppressMonetization;
