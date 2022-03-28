import { shallow } from 'enzyme';
import CharLimit from '../CharLimit';

describe('<CharLimit />', () => {
  it('should render component without errors when only passing charLimit prop', () => {
    const componentProps = {
      charLimit: 10,
    };
    const component = shallow(<CharLimit {...componentProps} />);
    const bar = component.find('.charLimit-count');

    expect(component).toMatchSnapshot();
    expect(bar.text()).toEqual('10');
    expect(bar.prop('style')).toEqual({ backgroundColor: '#6c7781' });
  });

  it('should have a grey bar when value is less than half of the character limit', () => {
    const componentProps = {
      showCharLimitAt: -1,
      charLimit: 10,
      charLimitValue: 'test',
    };
    const component = shallow(<CharLimit {...componentProps} />);
    const bar = component.find('.charLimit-count');

    expect(component).toMatchSnapshot();
    expect(bar.text()).toEqual('6'); // 10 (charLimit) -4 (charLimitValue length) = 6
    expect(bar.prop('style')).toEqual({ backgroundColor: '#6c7781' });
  });

  it('should have an orange bar when the character limit is reached', () => {
    const componentProps = {
      showCharLimitAt: -1,
      charLimit: 10,
      charLimitValue: '0123456789', // 10 characters to match charLimit
    };
    const component = shallow(<CharLimit {...componentProps} />);
    const bar = component.find('.charLimit-count');

    expect(component).toMatchSnapshot();
    expect(bar.text()).toEqual('0');
    expect(bar.prop('style')).toEqual({ backgroundColor: '#F56E28' });
  });

  it('should have a red bar when the character limit has been exceeded', () => {
    const componentProps = {
      showCharLimitAt: -1,
      charLimit: 10,
      charLimitValue: 'this is more than ten characters',
    };
    const component = shallow(<CharLimit {...componentProps} />);
    const bar = component.find('.charLimit-count');

    expect(component).toMatchSnapshot();
    expect(bar.text()).toEqual('-22'); // 10 (charLimit) -32 (charLimitValue length) = -22
    expect(bar.prop('style')).toEqual({ backgroundColor: '#DC3232' });
  });
});
