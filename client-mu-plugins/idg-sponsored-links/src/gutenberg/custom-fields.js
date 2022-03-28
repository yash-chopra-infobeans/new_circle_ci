const components = window?.IDGAssetManager?.components;
const { register } = window.CustomFields;

if (components?.TextWithDate) {
  const { TextWithDate } = components;

  register('fieldTypes', {
    name: 'date',
    render: ({ field, value, updateValue }) => (
      <TextWithDate label={field.title} value={value} hideLabel={false} onChange={updateValue} />
    ),
  });
}
