import PropTypes from 'prop-types';
import { has } from 'lodash-es';

import Message from './Message';
import { I18N_DOMAIN } from '../../settings';

const { __ } = wp.i18n;
const {
  Button,
  Card,
  CardBody,
  Panel,
  PanelBody,
  PanelRow,
  Dropdown,
  ButtonGroup,
  Flex,
  BaseControl,
} = wp.components;

const renderPanelTitle = (options, row, fallback) => {
  const { title_keys: titleKeys = [], title = false } = options;

  const keys = titleKeys.length > 0 ? titleKeys.filter(key => row[key]).map(key => row[key]) : [];

  return [`${title || fallback}`, ...keys].join(' | ');
};

const RepeaterItems = ({ rows, children, onDelete, field, errors }) => {
  const isPanel = field?.panel;
  const Container = isPanel ? Panel : Card;
  const Body = isPanel ? PanelBody : CardBody;

  return rows.map((row, index) => (
    <Container size="small">
      <Body
        className={field && field.className ? field.className : ''}
        icon="more"
        title={isPanel ? renderPanelTitle(field.panel, row, field.singular) : false}
        initialOpen={false}
        {...(has(errors, index) ? { opened: true } : {})}
      >
        {!isPanel ? children(index) : <PanelRow>{children(index)}</PanelRow>}
        {!field?.disable_removal && (
          <div className="cf-footer">
            <Dropdown
              position="bottom center"
              renderToggle={({ isOpen, onToggle }) => (
                <Button onClick={onToggle} isDestructive isSecondary isSmall aria-expanded={isOpen}>
                  {__('Remove', I18N_DOMAIN)} {field.removeBtn ? field.removeBtn : field.singular}
                </Button>
              )}
              renderContent={({ onClose }) => (
                <Flex alignItems="center">
                  <span className="cf-heading">{__('Are you sure?', I18N_DOMAIN)}</span>
                  <ButtonGroup>
                    <Button
                      isPrimary
                      isSmall
                      onClick={() => {
                        onDelete(index);
                        onClose();
                      }}
                    >
                      {__('Confirm', I18N_DOMAIN)}
                    </Button>
                    <Button isSecondary isSmall onClick={onClose}>
                      {__('Cancel', I18N_DOMAIN)}
                    </Button>
                  </ButtonGroup>
                </Flex>
              )}
            />
          </div>
        )}
      </Body>
    </Container>
  ));
};

RepeaterItems.propTypes = {
  rows: PropTypes.array.isRequired,
  children: PropTypes.func.isRequired,
  onDelete: PropTypes.func.isRequired,
  field: PropTypes.object,
};

const Repeater = ({ rows, onChange, children, field, error }) => (
  <BaseControl label={field?.title || ''}>
    {rows.length === 0 && (
      <Message
        message={`${field?.help ? field.help : `${__('No', I18N_DOMAIN)} ${field.plural}`}`}
      />
    )}
    <RepeaterItems
      rows={rows}
      onDelete={index => onChange(rows.filter((_, i) => i !== index))}
      field={field}
      errors={error}
    >
      {index => children(field.fields, `[${index}].`)}
    </RepeaterItems>
    {!field?.max_items && (
      <Button isPrimary isSmall onClick={() => onChange([...rows, {}])}>
        {__('Add', I18N_DOMAIN)} {field.addBtn ? field.addBtn : field.singular}
      </Button>
    )}
    {field?.max_items && rows.length < field?.max_items && (
      <Button isPrimary isSmall onClick={() => onChange([...rows, {}])}>
        {__('Add', I18N_DOMAIN)} {field.addBtn ? field.addBtn : field.singular}
      </Button>
    )}
  </BaseControl>
);

Repeater.propTypes = {
  rows: PropTypes.array.isRequired,
  onChange: PropTypes.func.isRequired,
  children: PropTypes.func.isRequired,
  field: PropTypes.object,
};

export default Repeater;
