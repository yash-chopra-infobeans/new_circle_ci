import { shallow } from 'enzyme';
import { Tab } from '../Tab';

const { createRef } = wp.element;
const refs = {
  prefixRef: createRef(),
  titleRef: createRef(),
};

export const exampleTab = {
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

describe('<Tab />', () => {
  it('should render without additionalFields', () => {
    const componentProps = {
      attributes: {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      getCurrentTitle: jest.fn(),
      getCurrentPlaceholder: jest.fn(),
      getCurrentPrefix: jest.fn(),
      handleTitleChange: jest.fn(),
      getCharLimitValue: jest.fn(),
      getCharLimit: jest.fn(),
      handleAdditionalFieldsChange: jest.fn(),
      getAdditionalFieldValue: jest.fn(),
      onPrefixBlur: jest.fn(),
      onTitleBlur: jest.fn(),
    };
    const component = shallow(<Tab {...componentProps} ref={refs}/>);

    expect(component).toMatchSnapshot();
  });

  it('should render additionalFields when passed as function', () => {
    exampleTab.additionalFields = () => <p>test additional field render</p>;

    const componentProps = {
      attributes: {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      getCurrentTitle: jest.fn(),
      getCurrentPlaceholder: jest.fn(),
      getCurrentPrefix: jest.fn(),
      handleTitleChange: jest.fn(),
      getCharLimitValue: jest.fn(),
      getCharLimit: jest.fn(),
      handleAdditionalFieldsChange: jest.fn(),
      getAdditionalFieldValue: jest.fn(),
      onPrefixBlur: jest.fn(),
      onTitleBlur: jest.fn(),
    };
    const component = shallow(<Tab {...componentProps} ref={refs}/>);

    expect(component).toMatchSnapshot();
    expect(component.find('p').text()).toEqual('test additional field render');
  });

  it('should render additionalFields when passed as object', () => {
    exampleTab.additionalFields = {
      render: () => <p>test additional field render</p>,
    };

    const componentProps = {
      attributes: {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
      },
      tabs: [exampleTab],
      tab: exampleTab,
      activeTab: 'headline',
      getCurrentTitle: jest.fn(),
      getCurrentPlaceholder: jest.fn(),
      getCurrentPrefix: jest.fn(),
      handleTitleChange: jest.fn(),
      getCharLimitValue: jest.fn(),
      getCharLimit: jest.fn(),
      handleAdditionalFieldsChange: jest.fn(),
      getAdditionalFieldValue: jest.fn(),
      onPrefixBlur: jest.fn(),
      onTitleBlur: jest.fn(),
    };
    const component = shallow(<Tab {...componentProps} ref={refs}/>);

    expect(component).toMatchSnapshot();
    expect(component.find('p').text()).toEqual('test additional field render');
  });
});
