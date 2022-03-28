import { isEmpty, get } from 'lodash-es';

import { I18N_DOMAIN } from '../../settings';

const { __ } = wp.i18n;
const { isURL } = wp.url;

export const validate = data => {
  const requiredData = ['asset_image_rights', 'meta.credit', 'alt'];
  const validateUrls = ['meta.credit_url'];
  let errors = {};

  requiredData.forEach(required => {
    if (isEmpty(get(data, required))) {
      if (!(required === 'alt' && data.meta.isAltRequired)) {
        errors = {
          ...errors,
          [required]: [...(errors?.[required] || []), __('Required field.', I18N_DOMAIN)],
        };
      }
    }
  });

  validateUrls.forEach(validateUrl => {
    if (!isEmpty(get(data, validateUrl)) && !isURL(get(data, validateUrl))) {
      errors = {
        ...errors,
        [validateUrl]: [...(errors?.[validateUrl] || []), __('Must be a valid url.', I18N_DOMAIN)],
      };
    }
  });

  return errors;
};

export default validate;
