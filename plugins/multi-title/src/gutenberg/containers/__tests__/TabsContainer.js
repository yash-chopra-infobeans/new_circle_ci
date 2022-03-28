import { shallow } from 'enzyme';
import { TabsContainer } from '../TabsContainer';
import { tabs } from './variables';

const { addFilter } = wp.hooks;

const externalChanges = [
  {
    name: 'headline',
    prefix: {
      metaKey: 'article_kicker',
    },
    title: 'Article Headline',
  },
  {
    inheritValueFrom: 'headline',
    metaKey: '_meta_title',
    name: 'seo',
    title: 'SEO',
  },
  {
    inheritValueFrom: 'headline',
    metaKey: 'article_social_headline',
    name: 'social',
    title: 'Social',
  },
  {
    inheritValueFrom: 'headline',
    metaKey: 'teaser_headline',
    name: 'teaser',
    prefix: {
      inheritValueFrom: 'headline',
      metaKey: 'teaser_kicker',
    },
    title: 'Teaser',
  },
];

describe('TabsContainer />', () => {
  beforeEach(() => {
    addFilter('multi_title_tabs', 'times-multi-title-tabs', () => tabs);
  });

  it('should update multi-title object and meta field(s) which inherit headline value/prefix when headline value/prefix is updated', () => {
    addFilter('multi_title_tabs', 'times-multi-title-tabs', () => externalChanges);

    const componentProps = {
      setAttributes: jest.fn(),
      unlockPostSaving: jest.fn(),
      editPost: jest.fn(),
      title: 'Updated Headline', // updated title ie headline
      titles: {
        headine: {
          prefix: 'Original Kicker',
          value: 'Original Headline',
        },
        seo: {
          prefix: 'Original Kicker',
          value: 'Original Headline',
        },
      },
      meta: {
        multi_title: '',
        article_kicker: 'Updated Kicker',
      },
      setBlockMetaFields: jest.fn(),
    };
    const component = shallow(<TabsContainer {...componentProps} />);

    expect(componentProps.editPost).toHaveBeenCalledWith({
      meta: {
        article_social_headline: 'Updated Headline',
        multi_title:
          '{"titles":{"headine":{"prefix":"Original Kicker","value":"Original Headline"},"seo":{"prefix":"Original Kicker","value":""},"headline":{"value":"Updated Headline","prefix":"Updated Kicker"},"social":{"value":"Updated Headline"},"teaser":{"value":"Updated Headline","prefix":"Updated Kicker"}}}',
        teaser_headline: 'Updated Headline',
        teaser_kicker: 'Updated Kicker',
      },
      title: 'Updated Headline',
    });
  });

  it('should render with first item in tabs array being the active tab when componens rendered', () => {
    const componentProps = {
      setAttributes: jest.fn(),
      unlockPostSaving: jest.fn(),
      editPost: jest.fn(),
      titles: {},
      meta: {
        multi_title: '',
      },
      setBlockMetaFields: jest.fn(),
    };
    const component = shallow(<TabsContainer {...componentProps} />);

    expect(component.state().activeTab).toEqual('headline');
  });

  it('should validate all defined tabs when validateTabs is called', () => {
    const componentProps = {
      setAttributes: jest.fn(),
      lockPostSaving: jest.fn(),
      unlockPostSaving: jest.fn(),
      editPost: jest.fn(),
      titles: {
        seo: {
          value: 'seo title',
        },
        shorttitle: {
          prefix: 'lon',
        },
        subtitle: {
          value: '12345',
          prefix: '123456',
        },
      },
      meta: {
        multi_title: '',
      },
      setBlockMetaFields: jest.fn(),
    };
    const component = shallow(<TabsContainer {...componentProps} />);

    component.instance().validateTabs();

    expect(component.state().errorMessages).toEqual({
      seo: {
        message: 'You have exceeded the maximum character limit.',
        preventPublish: true,
        title: 'SEO',
      },
      subtitle: {
        message: 'You have exceeded the maximum character limit.',
        preventPublish: true,
        title: 'Subtitle',
      },
    });
  });

  it('should validate passed tab when validateTab is called', () => {
    const componentProps = {
      setAttributes: jest.fn(),
      lockPostSaving: jest.fn(),
      unlockPostSaving: jest.fn(),
      editPost: jest.fn(),
      titles: {
        seo: {
          value: 'seo title',
        },
        shorttitle: {
          prefix: 'long prefix',
        },
        subtitle: {
          value: '12345',
          prefix: '123456',
        },
        strapline: {
          value: 'test title',
          prefix: 'test prefix',
        },
      },
      meta: {
        multi_title: '',
      },
      setBlockMetaFields: jest.fn(),
    };
    const component = shallow(<TabsContainer {...componentProps} />);

    expect(component.instance().validateTab(tabs[0])).toEqual({});
    expect(component.instance().validateTab(tabs[1])).toEqual({
      seo: {
        message: 'You have exceeded the maximum character limit.',
        preventPublish: true,
        title: 'SEO',
      },
    });
    expect(component.instance().validateTab(tabs[2])).toEqual({
      shorttitle: {
        message: 'You have exceeded the maximum character limit.',
        preventPublish: true,
        title: 'Short Title',
      },
    });
    expect(component.instance().validateTab(tabs[3])).toEqual({
      subtitle: {
        message: 'You have exceeded the maximum character limit.',
        preventPublish: true,
        title: 'Subtitle',
      },
    });
    expect(component.instance().validateTab(tabs[4])).toEqual({});
  });

  describe('Changing data', () => {
    it('should update standfirst on input change', () => {
      const componentProps = {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
        subtitles: {
          standfirst: '',
        },
        setAttributes: jest.fn(),
        editPost: jest.fn(),
        unlockPostSaving: jest.fn(),
        meta: {
          multi_title: '',
        },
        setBlockMetaFields: jest.fn(),
      };
      const component = shallow(<TabsContainer {...componentProps} />, {
        disableLifecycleMethods: true,
      });

      component.instance().handleSubtitleChange('standfirst')('this is a standfirst');

      expect(componentProps.editPost).toHaveBeenCalledTimes(1);
      expect(componentProps.editPost).toHaveBeenCalledWith({
        meta: {
          multi_title:
            '{"titles":{"headline":{"value":"test headline","prefix":"test prefix"}},"subtitles":{"standfirst":"this is a standfirst"}}',
        },
      });
    });
  });

  describe('componentDidUpdate', () => {
    it('should call validate tabs function when props are updated', () => {
      const componentProps = {
        setAttributes: jest.fn(),
        unlockPostSaving: jest.fn(),
        titles: {},
        meta: {
          multi_title: '',
        },
        setBlockMetaFields: jest.fn(),
      };
      const validateTabsSpy = jest.spyOn(TabsContainer.prototype, 'validateTabs');
      const component = shallow(<TabsContainer {...componentProps} />);

      component.setProps({
        titles: {
          headline: {
            value: 'test headline',
            preifx: 'test prefix',
          },
        },
      });

      // called 2 times, once on mount and once on prop change
      expect(validateTabsSpy).toHaveBeenCalledTimes(2);
    });
  });
});
