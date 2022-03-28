import { shallow } from 'enzyme';
import { Tabs } from '../Tabs';

describe('<Tabs />', () => {
  it('should render without errors', () => {
    const componentProps = {
      attributes: {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
      },
      errorMessages: {},
      activeTab: 'headline',
      selectTab: jest.fn(),
      tabs: [],
      onError: jest.fn(),
      handleSubtitleChange: jest.fn(),
    };
    const component = shallow(<Tabs {...componentProps} />);
    const slot = component.find('Slot');

    expect(component).toMatchSnapshot();
    expect(slot.prop('name')).toEqual('multi-title-below-tabs');
  });

  it('should render with errors', () => {
    const componentProps = {
      attributes: {
        titles: {
          headline: {
            value: 'test headline',
            prefix: 'test prefix',
          },
        },
      },
      errorMessages: {
        seo: {
          message: 'You have exceeded the maximum character limit.',
          preventPublish: true,
          title: 'SEO',
        },
      },
      activeTab: 'headline',
      selectTab: jest.fn(),
      tabs: [],
      onError: jest.fn(),
      handleSubtitleChange: jest.fn(),
    };
    const component = shallow(<Tabs {...componentProps} />);
    const errorTitle = component.find('.publishErrors-section p strong');
    const errorText = component.find('.publishErrors-section ul li');

    expect(component).toMatchSnapshot();
    expect(errorTitle.text()).toEqual('SEO');
    expect(errorText.text()).toEqual('You have exceeded the maximum character limit.');
  });
});
