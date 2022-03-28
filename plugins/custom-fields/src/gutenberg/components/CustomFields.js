import PropTypes from 'prop-types';
import { has } from 'lodash-es';
import { NAMESPACE } from '../../settings';

import Tabs from './Tabs';
import Fields from './Fields';

const { Slot } = wp.components;

const CustomFields = ({ sections, fieldGroups, values, errors }) => {
  const tabs = section =>
    section?.tabs?.map(tab => ({
      ...tab,
      className:
        has(errors, section.name) && has(errors[section.name], tab.name) ? 'has-error' : '',
    }));

  return (
    <>
      {sections.map(section => (
        <div className="cf-section">
          {section?.title && <h2 className="cf-title">{section.title}</h2>}
          <Slot name={`${NAMESPACE}/before-section-${section.name}`} />
          <Tabs tabs={tabs(section)}>
            {tab =>
              fieldGroups
                .filter(fieldGroup => fieldGroup.sections.includes(section.name))
                .map(fieldGroup => (
                  <div className="cf-fieldGroup">
                    {fieldGroup?.title && <h2 className="cf-subTitle">{fieldGroup.title}</h2>}
                    <Fields
                      fields={fieldGroup.fields}
                      sectionKey={section.name}
                      scope={tab ? `${tab.name}.${fieldGroup.name}.` : `${fieldGroup.name}.`}
                      tabName={tab?.name}
                      errors={errors}
                      values={values}
                    />
                  </div>
                ))
            }
          </Tabs>
          <Slot name={`${NAMESPACE}/after-section-${section.name}`} />
        </div>
      ))}
    </>
  );
};

CustomFields.propTypes = {
  sections: PropTypes.array.isRequired,
  fieldGroups: PropTypes.array.isRequired,
};

export default CustomFields;
