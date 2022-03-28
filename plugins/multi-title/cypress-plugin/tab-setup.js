const { addFilter } = wp.hooks;
const { WrappedInput } = window.MultiTitle;
const { Fragment, createElement: el } = wp.element;

function AdditionalFields({ handleChange, tab, getValue }) {
  return el(
    Fragment,
    {},
    el(WrappedInput, {
      rows: 1,
      placeholder: 'Keywords',
      value: getValue(tab.name, 'multi_title_seokeywords'),
      className: `title-input title-input-${tab.name} title-input-sm`,
      onChange: handleChange(tab, 'multi_title_seokeywords', true),
    }),
  );
}

addFilter('multi_title_tabs', 'multi-title-test-setup', () => [
  {
    name: 'headline',
    title: 'Headline',
    className: 'tab tab-headline',
    charLimit: 15,
    blockPublishOnError: true,
    placeholder: 'Add article title',
    prefix: {
      enabled: true,
      placeholder: 'Add prefix',
    },
  },
  {
    name: 'seo',
    title: 'SEO',
    inheritValueFrom: 'headline',
    additionalFields: {
      render: function (attrs) {
        return el(AdditionalFields, {
          ...attrs,
        });
      },
      metaKeys: ['multi_title_seokeywords'],
    },
  },
  {
    name: 'placeholder',
    title: 'Placeholder',
    inheritPlaceholderFrom: 'headline',
  },
  {
    name: 'prefix',
    title: 'Prefix',
    inheritPlaceholderFrom: 'headline',
    prefix: {
      enabled: true,
      charLimit: 15,
      inheritValueFrom: 'headline',
      placeholder: 'Add prefix',
    },
  },
  {
    name: 'combinedChar',
    title: 'Combined Char',
    charLimit: 5,
    combineCharLimit: true,
    prefix: {
      enabled: true,
      charLimit: 10,
    },
  },
]);
