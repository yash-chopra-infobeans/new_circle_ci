const { Component } = wp.element;
const { TextControl, TextareaControl } = wp.components;
const { dispatch } = wp.data;

// eslint-disable-next-line react/prefer-stateless-function
class AdditionalHeadlineFields extends Component {
  componentDidUpdate(prevProps) {
    const { titles, tab, subtitles } = this.props;
    const { titles: prevTitles } = prevProps;

    const currentTitle = titles?.[tab?.name]?.value || '';
    const prevTitle = prevTitles?.[tab?.name]?.value || '';
    const shortTitle = titles?.[tab?.name]?.additional?.short_title || '';
    const currentDesc = titles?.[tab?.name]?.additional?.headline_desc || '';
    const prevDesc = prevTitles?.[tab?.name]?.additional?.headline_desc || '';
    const currentSeoDesc = titles?.seo?.additional?.seo_desc || '';
    const currentSocialDesc = titles?.social?.additional?.social_desc || '';
    let newTitles = Object.assign(titles);

    // Update short title when headline is changed if there were previously equal.
    if (currentTitle !== prevTitle && shortTitle === prevTitle) {
      newTitles = {
        ...newTitles,
        headline: {
          ...newTitles?.headline,
          additional: {
            ...newTitles?.headline?.additional,
            short_title: currentTitle,
          },
        },
      };
    }

    // Update seo description when headline description is changed if there were previously equal.
    if (currentDesc !== prevDesc && prevDesc === currentSeoDesc) {
      newTitles = {
        ...newTitles,
        seo: {
          ...newTitles?.seo,
          additional: {
            ...newTitles?.seo?.additional,
            seo_desc: currentDesc,
          },
        },
      };
    }

    // Update social description when headline description is changed if there were previously equal.
    if (currentDesc !== prevDesc && prevDesc === currentSocialDesc) {
      newTitles = {
        ...newTitles,
        social: {
          ...newTitles?.social,
          additional: {
            ...newTitles?.social?.additional,
            social_desc: currentDesc,
          },
        },
      };
    }

    dispatch('core/editor').editPost({
      meta: {
        multi_title: JSON.stringify({
          titles: newTitles,
          subtitles,
        }),
      },
    });
  }

  render() {
    const { tab, getValue = '', handleChange } = this.props;

    return (
      <div className="additional-fields__container">
        <TextControl
          label="Short Title"
          value={getValue(tab.name, 'short_title')}
          onChange={handleChange(tab, 'short_title')}
        />
        <TextControl
          label="Subheadline"
          value={getValue(tab.name, 'headline_subheadline')}
          onChange={handleChange(tab, 'headline_subheadline')}
        />
        <TextareaControl
          label="Description"
          value={getValue(tab.name, 'headline_desc')}
          onChange={handleChange(tab, 'headline_desc', true)}
        />
      </div>
    );
  }
}

export default AdditionalHeadlineFields;
