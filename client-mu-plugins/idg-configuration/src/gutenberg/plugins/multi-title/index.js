import './styles.scss';
import AdditionalHeadlineFields from './AdditionalHeadlineFields';
import { AdditionalSEOFields, getSluggedValue } from './AdditionalSEOFields';
import AdditionalSocialFields from './AdditionalSocialFields';

const { addFilter } = wp.hooks;
const { dispatch } = wp.data;

addFilter('multi_title_tabs', 'idg-configuration', () => [
  {
    name: 'headline',
    title: 'Headline',
    className: 'headline-css',
    isTitle: true,
    charLimit: 256,
    blockPublishOnError: true,
    additionalFields: {
      render: attrs => <AdditionalHeadlineFields {...attrs} />,
    },
  },
  {
    name: 'seo',
    title: 'SEO',
    className: 'seo-css',
    charLimit: 70,
    inheritValueFrom: 'headline',
    onTitleChange: (title, tab, getValue, handleChange) => {
      const sluggedValue = getSluggedValue(title);

      handleChange(tab, 'seo_slug', true)(sluggedValue);
      dispatch('core/editor').editPost({
        slug: sluggedValue,
      });

      return title;
    },
    additionalFields: {
      render: attrs => <AdditionalSEOFields {...attrs} />,
    },
  },
  {
    name: 'social',
    title: 'Social',
    className: 'social-css',
    charLimit: 70,
    inheritValueFrom: 'headline',
    additionalFields: {
      render: attrs => <AdditionalSocialFields {...attrs} />,
    },
  },
]);
