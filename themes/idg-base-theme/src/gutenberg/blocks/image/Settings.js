import { assign } from 'lodash-es';

const Attributes = (settings, name) => {
  if (name !== 'core/image') {
    return settings;
  }

  const attributes = assign(settings.attributes, {
    credit: {
      type: 'string',
    },
    creditUrl: {
      type: 'string',
    },
  });

  return { ...settings, attributes, styles: [] };
};

export default Attributes;
