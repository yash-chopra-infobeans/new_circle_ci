import { WINDOW_NAMESPACE } from '../settings';
import Repeater from './components/Repeater';
import RichText from './components/RichText';
import Handlebars from './components/Handlebars';
import ImageUpload from './components/ImageUpload';

const { TextControl, TextareaControl, SelectControl, FormToggle, BaseControl } = wp.components;
const { register } = window[WINDOW_NAMESPACE];

register('fieldTypes', {
  name: 'text',
  render: ({ field, value, updateValue }) => (
    <TextControl
      label={field.title}
      type="text"
      value={value}
      help={field?.help}
      onChange={updateValue}
    />
  ),
});

register('fieldTypes', {
  name: 'textarea',
  render: ({ field, value, updateValue }) => (
    <TextareaControl label={field.title} help={field?.help} value={value} onChange={updateValue} />
  ),
});

register('fieldTypes', {
  name: 'number',
  render: ({ field, value, updateValue }) => (
    <TextControl
      label={field.title}
      help={field?.help}
      type="number"
      value={value}
      onChange={updateValue}
    />
  ),
});

register('fieldTypes', {
  name: 'select',
  render: ({ field, value = null, updateValue }) => {
    if (field.options[0].value && !field.options[0]?.disabled) {
      field.options.unshift({
        label: `Select ${field.title}`,
        value: '',
        disabled: true,
      });
    }

    return (
      <SelectControl
        label={field.title}
        value={value}
        options={field.options}
        help={field?.help}
        onChange={updateValue}
      />
    );
  },
});

register('fieldTypes', {
  name: 'toggle',
  render: ({ field, value, updateValue }) => (
    <BaseControl label={field.title} help={field?.help} className="cf-baseControl">
      <FormToggle checked={value} onChange={() => updateValue(!value)} />
    </BaseControl>
  ),
});

register('fieldTypes', {
  name: 'repeater',
  render: ({ field, value, updateValue, children, ...props }) => (
    <Repeater field={field} onChange={updateValue} rows={value || []} {...props}>
      {children}
    </Repeater>
  ),
});

register('fieldTypes', {
  name: 'handlebars',
  render: ({ value, updateValue, field }) => (
    <BaseControl label={field?.title || ''} help={field?.help}>
      <Handlebars
        value={value}
        onChange={updateValue}
        field={field}
        handlebars={field?.variables}
      />
    </BaseControl>
  ),
});

register('fieldTypes', {
  name: 'richtext',
  render: ({ value, updateValue, field }) => (
    <BaseControl label={field?.title || ''} help={field?.help}>
      <RichText value={value} onChange={updateValue} field={field} />
    </BaseControl>
  ),
});

register('fieldTypes', {
  name: 'image',
  render: ({ value, updateValue, field }) => (
    <BaseControl label={field?.title || ''}>
      <ImageUpload onChange={updateValue} value={value} />
    </BaseControl>
  ),
});
