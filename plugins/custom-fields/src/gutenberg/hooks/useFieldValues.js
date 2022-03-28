import { set, get, has } from 'lodash-es';

import { WINDOW_NAMESPACE } from '../../settings';
import parseJSON from '../utils/parseJSON';

const { entity } = window[WINDOW_NAMESPACE];

const { useEntityProp } = wp.coreData;

const useFieldValues = () => {
  const [values, setValues] = useEntityProp(entity.kind, entity.name, entity.prop);

  const setValue = (section, key, value) => {
    const sectionValues = parseJSON(has(values, section) ? values[section] : {});

    set(sectionValues, key, value);

    setValues({
      ...values,
      [section]: JSON.stringify(sectionValues),
    });
  };

  const getValue = (section, key) =>
    !has(values, section) ? undefined : get(parseJSON(values[section]), key) || '';

  return { values, setValues, getValue, setValue };
};

export default useFieldValues;
