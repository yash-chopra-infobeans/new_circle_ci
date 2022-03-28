import { shallow } from 'enzyme';
import { TabContainer } from '../TabContainer';
import { exampleTab as TabOne, exampleTabTwo as TabTwo } from './variables';

let exampleTab = TabOne;
let exampleTabTwo = TabTwo;

afterEach(() => {
  exampleTab = {
    name: 'headline',
    title: 'Headline 2',
    className: 'tab tab-headline',
    metaKey: 'multi_title_headline',
    combineCharLimit: true,
    prefix: {
      enabled: true,
      metaKey: 'multi_title_headline_prefix',
    },
  };
});

describe('Changing data', () => {
  it("should update tab 'value' property when handleTitleChange is called", () => {
    const componentProps = {
      titles: {
        headline: {
          prefix: 'this is a prefix',
        },
      },
      titleTab: exampleTab,
      tabs: [exampleTab, exampleTabTwo],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };
    const component = shallow(<TabContainer {...componentProps} />);

    component.instance().handleTitleChange(exampleTab, 'value')('test new headline');

    // check the function was fired
    expect(componentProps.editPost).toHaveBeenCalledTimes(1);
    // check the arguments that were passed to confirm new attrs are correct
    expect(componentProps.editPost).toHaveBeenCalledWith({
      meta: {
        multi_title_headline: 'test new headline',
        multi_title_seo: 'test new headline',
        multi_title:
          '{"titles":{"headline":{"prefix":"this is a prefix","value":"test new headline"},"seo":{"value":"test new headline"}}}',
      },
      title: 'test new headline',
    });
  });

  it("should update tab 'prefix' property when handleTitleChange is called", () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'this is a headline',
        },
      },
      titleTab: exampleTab,
      tabs: [exampleTab, exampleTabTwo],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };
    const component = shallow(<TabContainer {...componentProps} />);

    component.instance().handleTitleChange(exampleTab, 'prefix')('test new prefix');

    // check the function was fired
    expect(componentProps.editPost).toHaveBeenCalledTimes(1);
    // check the arguments that were passed to confirm new attrs are correct
    expect(componentProps.editPost).toHaveBeenCalledWith({
      meta: {
        multi_title_headline_prefix: 'test new prefix',
        multi_title_seo_prefix: 'test new prefix',
        multi_title:
          '{"titles":{"headline":{"value":"this is a headline","prefix":"test new prefix"},"seo":{"prefix":"test new prefix"}}}',
      },
      title: 'this is a headline',
    });
  });

  it('should update tab additional field value when handleTitleChange is called', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
        seotitle: {
          value: 'seo headline',
          prefix: 'seo prefix',
        },
      },
      titleTab: exampleTab,
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };
    const component = shallow(<TabContainer {...componentProps} />);

    component.instance().handleAdditionalFieldsChange(exampleTab, 'meta_headline')('test');

    // check the function was fired
    expect(componentProps.editPost).toHaveBeenCalledTimes(1);
    // check the arguments that were passed to confirm new attrs are correct
    expect(componentProps.editPost).toHaveBeenCalledWith({
      meta: {
        multi_title:
          '{"titles":{"headline":{"value":"test headline","prefix":"test prefix","additional":{"meta_headline":"test"}},"seotitle":{"value":"seo headline","prefix":"seo prefix"}}}',
      },
      title: 'test headline',
    });
  });

  it('should update individual additional meta field with new value', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
        seotitle: {
          value: 'seo headline',
          prefix: 'seo prefix',
        },
      },
      titleTab: exampleTab,
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };

    const component = shallow(<TabContainer {...componentProps} />);

    component.instance().handleAdditionalFieldsChange(exampleTab, 'meta_headline', true)('test');

    expect(componentProps.editPost).toHaveBeenCalledWith({
      meta: {
        meta_headline: 'test',
        multi_title:
          '{"titles":{"headline":{"value":"test headline","prefix":"test prefix","additional":{"meta_headline":"test"}},"seotitle":{"value":"seo headline","prefix":"seo prefix"}}}',
      },
      title: 'test headline',
    });
  });
});

describe('Get tab placeholder', () => {
  const componentProps = {
    titles: {
      headline: {
        value: 'test headline',
        prefix: 'test prefix',
      },
      seotitle: {
        value: 'seo headline',
        prefix: 'seo prefix',
      },
    },
    tabs: [exampleTab],
    tab: exampleTab,
    activeTab: 'headline',
  };
  const component = shallow(<TabContainer {...componentProps} />);

  it('should return tab name as placeholder', () => {
    expect(component.instance().getCurrentPlaceholder(exampleTab)).toEqual('Headline 2');
  });

  it('should return tab placeholder value as placeholder', () => {
    exampleTab.placeholder = 'test placeholder';
    expect(component.instance().getCurrentPlaceholder(exampleTab)).toEqual('test placeholder');
  });

  it('should return value from inherited tab as placeholder', () => {
    exampleTab.inheritPlaceholderFrom = 'seotitle';
    expect(component.instance().getCurrentPlaceholder(exampleTab)).toEqual('seo headline');
  });
});

describe('Get tab title value', () => {
  it('should return title value', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    expect(component.instance().getCurrentTitle(exampleTab)).toEqual('test headline');
  });

  it('should return title value inherited from another tab', () => {
    exampleTab.name = 'seo';
    exampleTab.inheritValueFrom = 'headline';

    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    expect(component.instance().getCurrentTitle(exampleTab)).toEqual('test headline');
  });

  it('should return title value inherited from another tab via callback', () => {
    exampleTab.name = 'seo';
    exampleTab.inheritValueFrom = () => 'headline';

    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    expect(component.instance().getCurrentTitle(exampleTab)).toEqual('test headline');
  });

  it.each([[''], [undefined]])(
    'should return current title value if inheritance callback returns empty "%s"',
    inheritTabName => {
      exampleTab.name = 'seo';
      exampleTab.inheritValueFrom = () => inheritTabName;

      const componentProps = {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
          seo: {
            value: 'seo headline',
          },
        },
        tabs: [exampleTab],
        tab: exampleTab,
        activeTab: 'headline',
      };
      const component = shallow(<TabContainer {...componentProps} />);

      expect(component.instance().getCurrentTitle(exampleTab)).toEqual('seo headline');
    },
  );
});

describe('Get tab additional field value', () => {
  it("should return empty string if value doesn't exist in attributes or meta", () => {
    exampleTab.metaKey = 'meta_headline';

    const componentProps = {
      titles: {},
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };
    const component = shallow(<TabContainer {...componentProps} />);

    // return empty string if values doesn't exist in attributes or meta
    expect(component.instance().getAdditionalFieldValue('headliine', 'meta_headline')).toEqual('');
  });

  it('should return value from multi-title object', () => {
    exampleTab.metaKey = 'meta_headline';

    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
          additional: {
            meta_headline: 'meta add',
          },
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      setAttributes: jest.fn(),
      editPost: jest.fn(),
    };
    const component = shallow(<TabContainer {...componentProps} />);

    // return value in attributes if it exists
    expect(component.instance().getAdditionalFieldValue('headline', 'meta_headline')).toEqual(
      'meta add',
    );
  });
});

describe('Combined chacracter limit methods', () => {
  it('should return combined character limit', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    exampleTab.prefix.charLimit = 10;
    exampleTab.charLimit = 15;

    expect(component.instance().getCharLimit(exampleTab, 'prefix')).toEqual(25);
    expect(component.instance().getCharLimit(exampleTab, 'value')).toEqual(25);
  });

  it('should return non-combined character limit', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    exampleTab.prefix.charLimit = 10;
    exampleTab.charLimit = 15;
    exampleTab.combineCharLimit = false;

    expect(component.instance().getCharLimit(exampleTab, 'prefix')).toEqual(10);
    expect(component.instance().getCharLimit(exampleTab, 'value')).toEqual(15);
  });

  it('should return value if tab has combineCharLimit', () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    expect(component.instance().getCharLimitValue(exampleTab, 'prefix')).toEqual('test prefix');
    expect(component.instance().getCharLimitValue(exampleTab, 'value')).toEqual('test headline');
  });

  it("should return empty if tab doesn't have combineCharLimit", () => {
    const componentProps = {
      titles: {
        headline: {
          value: 'test headline',
          prefix: 'test prefix',
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
    };
    const component = shallow(<TabContainer {...componentProps} />);

    exampleTab.combineCharLimit = false;

    expect(component.instance().getCharLimitValue(exampleTab, 'prefix')).toEqual('');
    expect(component.instance().getCharLimitValue(exampleTab, 'value')).toEqual('');
  });
});

describe('setInheritedValue', () => {
  const componentProps = {
    titles: {
      headline: {
        value: 'test headline',
        prefix: 'test prefix',
      },
    },
    tabs: [exampleTabTwo],
    tab: exampleTabTwo,
    activeTab: 'headline',
    setAttributes: jest.fn(),
    editPost: jest.fn(),
  };

  it('should call handleTitleChange if tab title input inherits a value and the value is empty', () => {
    const component = shallow(<TabContainer {...componentProps} />);
    const handleTitleChangeValueSpy = jest.spyOn(component.instance(), 'handleTitleChange');

    component.instance().setInheritedValue(exampleTabTwo, 'value')('');

    expect(handleTitleChangeValueSpy).toHaveBeenCalledTimes(1);
  });
});
