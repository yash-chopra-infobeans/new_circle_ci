import { isNull } from 'lodash';

const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { CheckboxControl } = wp.components;
const { useEntityProp } = wp.coreData;
const { useSelect } = wp.data;

const META_KEY = 'suppress_html_meta';

const INTEGRATIONS = [
  {
    label: __('Canonical URL', 'idg-base-theme'),
    metaTarget: 'canonical_url',
  },
  {
    label: __('Open Graph', 'idg-base-theme'),
    metaTarget: 'open_graph',
  },
  {
    label: __('Twitter', 'idg-base-theme'),
    metaTarget: 'twitter',
  },
  {
    label: __('Pagination', 'idg-base-theme'),
    metaTarget: 'pagination',
  },
  {
    label: __('Source', 'idg-base-theme'),
    metaTarget: 'source',
  },
  {
    label: __('Date', 'idg-base-theme'),
    metaTarget: 'date',
  },
  {
    label: __('Description', 'idg-base-theme'),
    metaTarget: 'description',
  },
];

const SuppressMeta = () => {
  const postType = useSelect(select => {
    return select('core/editor').getCurrentPostType();
  }, []);

  if (!['post', 'page'].includes(postType)) {
    return null;
  }

  const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
  const parsedMeta = meta[META_KEY] ? JSON.parse(meta[META_KEY]) : false;

  const toggle = metaTarget => {
    if (parsedMeta[metaTarget]) {
      delete parsedMeta[metaTarget];
    } else {
      parsedMeta[metaTarget] = true;
    }

    setMeta({
      [META_KEY]: JSON.stringify({ ...parsedMeta }),
    });
  };

  const isChecked = metaTarget => {
    let parsedVal = false;
    if (parsedMeta && !isNull(parsedMeta)) {
      parsedVal = parsedMeta[metaTarget];
    }
    return parsedVal;
  };

  return (
    <PluginDocumentSettingPanel
      name="suppress-meta"
      title={__('Suppress HTML Meta', 'idg-base-theme')}
    >
      {INTEGRATIONS.map(({ label, metaTarget }) => (
        <CheckboxControl
          label={label}
          checked={isChecked(metaTarget)}
          onChange={() => toggle(metaTarget)}
        />
      ))}
      <span>{__('These MUST only be enabled by authorized users.', 'idg-base-theme')}</span>
    </PluginDocumentSettingPanel>
  );
};

export default SuppressMeta;
