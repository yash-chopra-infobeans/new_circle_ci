import { shallow, mount } from 'enzyme';
import { WrappedInput } from '../WrappedInput';

describe('WrappedInput class', () => {
  it('should render without errors', () => {
    const componentProps = {
    };
    const component = shallow(<WrappedInput {...componentProps} />);

    expect(component).toMatchSnapshot();
  });

  it('should render error in UI', () => {
    const componentProps = {};
    const component = shallow(<WrappedInput {...componentProps} />);

    component.setState({
      errors: true,
      errorMessages: ['You have exceeded the maximum character limit.'],
    });

    const error = component.find('.wrappedInput-errors ul li');

    expect(component).toMatchSnapshot();
    expect(error.text()).toEqual('You have exceeded the maximum character limit.');
  });

  it('should set any additional props as input attribute(s)', () => {
    const componentProps = {
      additionalprop: 'some data',
    };
    const component = mount(<WrappedInput {...componentProps} />);
    const input = component.find('textarea').at(0);

    expect(input.prop('additionalprop')).toEqual('some data');
  });

  describe('input validation', () => {
    it('should clear any errors in state on succesfully validating input', () => {
      const componentProps = {
        charLimit: 5,
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      component.setState({
        errors: true,
        errorMessages: ['You have exceeded the maximum character limit.'],
      });

      expect(component.instance().validateInput('test')).toEqual({
        errors: false,
        errorMessages: [],
      });
    });

    it('should set error state when character limit exceeded', () => {
      const componentProps = {
        charLimit: 5,
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      expect(component.instance().validateInput('morethan5chars')).toEqual({
        errors: true,
        errorMessages: ['You have exceeded the maximum character limit.'],
      });
    });

    it('should call onError when passed as prop and character limit exceeded', () => {
      const componentProps = {
        charLimit: 5,
        onError: jest.fn(),
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      component.instance().validateInput('morethan5chars');
      expect(componentProps.onError).toHaveBeenCalledTimes(1);
    });
  });

  describe('input interactions', () => {
    it('should update state when value changes', () => {
      const componentProps = {
        onChange: jest.fn(),
        name: 'test',
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      component.instance().onChange('more');
      expect(component.state().values).toEqual({
        additional: {
          test: "more",
        }
      });
    });

    it('should update state with returned value from onChange prop when value changes', () => {
      const componentProps = {
        onChange: () => 'test value',
        name: 'test',
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      component.instance().onChange('more');
      expect(component.state().values).toEqual({
        additional: {
          test: "test value",
        }
      });
    });

    it('should call onChange prop when value changes', () => {
      const componentProps = {
        onChange: jest.fn(),
      };
      const component = shallow(<WrappedInput {...componentProps} />);

      component.instance().onChange('more');
      expect(componentProps.onChange).toHaveBeenCalledTimes(1);
    });

    it('should call onFocus when input is in focus', () => {
      const componentProps = {
        onFocus: jest.fn(),
      };
      const component = mount(<WrappedInput {...componentProps} />);
      const input = component.find('textarea').at(0);

      input.simulate('focus');

      expect(componentProps.onFocus).toHaveBeenCalledTimes(1);
    });

    it('should call onBlur when input has lossed focus', () => {
      const componentProps = {
        onBlur: jest.fn(),
        onChange: jest.fn(),
      };
      const component = mount(<WrappedInput {...componentProps} />);
      const input = component.find('textarea').at(0);

      input.simulate('focus');
      input.simulate('change');
      input.simulate('blur');

      expect(componentProps.onBlur).toHaveBeenCalledTimes(1);
    });

    it('should display character limit bar on input focus', () => {
      const componentProps = {
        onBlur: jest.fn(),
        onChange: jest.fn(),
        showCharLimitOnFocus: true,
        value: 'ss',
      };
      const component = mount(<WrappedInput {...componentProps} />);
      const input = component.find('textarea').at(0);

      expect(component.state().charLimitDisplay).toEqual(false);

      input.simulate('focus');

      expect(component.state().charLimitDisplay).toEqual(true);

      input.simulate('change');
      input.simulate('blur');

      expect(component.state().charLimitDisplay).toEqual(false);
    });
  });
});
