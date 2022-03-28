import PropTypes from 'prop-types';
import { get, isUndefined } from 'lodash-es';

import { WINDOW_NAMESPACE } from '../../settings';

const { fieldTypes } = window[WINDOW_NAMESPACE];

const CONDITIONS = {
  '===': (a, b) => a === b,
  '!==': (a, b) => a !== b,
  '<': (a, b) => a < b,
  '>': (a, b) => a > b,
  '<=': (a, b) => a <= b,
  '>=': (a, b) => a >= b,
};

const getDefaultValue = (field, tabName) => {
  if (tabName && field?.default_tabs) {
    return get(field.default_tabs, tabName) || '';
  }

  return field?.default || '';
};

const Fields = ({ fields, sectionKey, scope, tabName = false, values, errors }) => {
  const { getValue, setValue } = values;

  const shouldRenderField = field => {
    if (!field.exclude_from) {
      return true;
    }

    if (tabName && field.exclude_from.includes(tabName)) {
      return false;
    }

    if (field.exclude_from.includes(sectionKey)) {
      return false;
    }

    return true;
  };

  return (
    <div class="cf-fieldsContainer">
      {fields.filter(shouldRenderField).map(({ type, width, key, conditions, ...config }) => {
        if (config?.hidden) {
          return null;
        }

        const FieldComponent = fieldTypes[type];

        if (!FieldComponent) {
          return null;
        }

        const fieldKey = `${scope}${key}`;
        const error = get(errors, `${sectionKey}.${fieldKey}`);

        const hasError = error && (typeof error === 'string' || error instanceof String);
        const value = getValue(sectionKey, fieldKey);

        if (conditions) {
          const conditionMet = conditions.some(condition => {
            const conditionalValue = getValue(sectionKey, `${scope}${condition.key}`);
            return CONDITIONS[condition.operator](condition.value, conditionalValue);
          });

          if (!conditionMet) {
            return null;
          }
        }

        return (
          <div
            className={`cf-field ${hasError ? 'has-error ' : ''}${type}`}
            style={width ? { flexBasis: `${width}%` } : { flexGrow: 1 }}
          >
            <FieldComponent
              field={config}
              error={error}
              section={sectionKey}
              scope={scope}
              key={key}
              getValue={getValue}
              value={isUndefined(value) ? getDefaultValue(config, tabName) : value || ''}
              updateValue={fieldValue => setValue(sectionKey, fieldKey, fieldValue)}
              tabName={tabName}
            >
              {(childFields, newScope) => (
                <Fields
                  fields={childFields}
                  sectionKey={sectionKey}
                  scope={`${scope}${key}${newScope}`}
                  tabName={tabName}
                  errors={errors}
                  values={values}
                />
              )}
            </FieldComponent>
            {hasError && <span className="cf-field-errorMessage">{error}</span>}
          </div>
        );
      })}
    </div>
  );
};

Fields.propTypes = {
  fields: PropTypes.array.isRequired,
  sectionKey: PropTypes.string.isRequired,
  scope: PropTypes.string.isRequired,
  tabName: PropTypes.string,
};

export default Fields;
