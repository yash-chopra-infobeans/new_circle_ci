import classNames from 'classnames';
import TextareaAutosize from 'react-autosize-textarea';
import withForwardedRef from '../utils/forwardRef';
import { CharLimit } from './CharLimit';

const { __ } = wp.i18n;
const { Component } = wp.element;

export class WrappedInput extends Component {
  constructor(...props) {
    super(...props);

    this.state = {
      charLimitDisplay: true,
      errors: false,
      errorMessages: [],
    };
  }

  componentDidMount() {
    const { showCharLimitOnFocus } = this.props;

    // hide character limit bar if showCharLimitOnFocus is passed as prop
    if (showCharLimitOnFocus) {
      this.setState({
        charLimitDisplay: false,
      });
    }
  }

  onChange = value => {
    const { name, titleType = 'additional', onChange, charLimitValue = '' } = this.props;

    /** Check input value meets criteria passing in any other characters (charLimitValue)
     * that should be taken into account when validating the input value.
     */
    const validation = this.validateInput(`${charLimitValue}${value}`);

    this.setState({
      values: {
        [titleType]: {
          [name]: onChange(value, validation) || value,
        },
      },
    });
  };

  decodeHTML = value => {
    const text = document.createElement('textarea');
    text.innerHTML = value;

    return text.value;
  };

  validateInput(value) {
    const { charLimit = false, onError } = this.props;
    let errors = false;
    let errorMessages = [];

    // if character limit and value length is greater set appropriate error message
    if (charLimit && value.length > charLimit) {
      errors = true;
      errorMessages = [
        ...errorMessages,
        __('You have exceeded the maximum character limit.', 'multi-title'),
      ];
    }

    // if errors and onError function has been passed call it passing in error state and messages
    if (errors && onError) {
      onError(errors, errorMessages);
    }

    return {
      errors,
      errorMessages,
    };
  }

  render() {
    const {
      name,
      titleType = 'additional',
      charLimit = false,
      charLimitValue = '',
      className = '',
      hideErrors = false,
      onBlur,
      onFocus,
      showCharLimitAt = -1,
      showCharLimitOnFocus,
      value = '',
      rows = 1,
      forwardedRef,
      ...inputProperties
    } = this.props;

    const { values: stateValue = [], charLimitDisplay, errors, errorMessages } = this.state;

    const charLimitClasses = classNames(className, 'wrappedInput', {
      'charLimit--visible':
        charLimitDisplay && charLimit && `${charLimitValue}${value}`.length >= showCharLimitAt,
    });

    let inputValue = value;

    if (stateValue?.[titleType]?.[name]) {
      inputValue = stateValue[titleType][name];
    }

    inputValue = this.decodeHTML(inputValue);

    return (
      <div className={charLimitClasses}>
        <CharLimit
          charLimit={charLimit}
          charLimitValue={`${charLimitValue}${value}`}
          showCharLimitAt={showCharLimitAt}
        />
        <div className="wrappedInput-input">
          <TextareaAutosize
            {...inputProperties}
            name={`${name}-${titleType}`}
            ref={forwardedRef}
            rows={rows}
            value={inputValue}
            onChange={e => this.onChange(e.target.value)}
            onBlur={() => {
              this.setState({
                values: {},
              });

              if (showCharLimitOnFocus) {
                this.setState({
                  charLimitDisplay: false,
                });
              }

              if (onBlur) {
                onBlur(value);
              }
            }}
            onFocus={() => {
              if (showCharLimitOnFocus) {
                this.setState({
                  charLimitDisplay: true,
                });
              }

              if (onFocus) {
                onFocus(value);
              }
            }}
          />
        </div>
        {errors && !hideErrors && (
          <div className="wrappedInput-errors">
            <ul>
              {errorMessages.map(message => (
                <li>{message}</li>
              ))}
            </ul>
          </div>
        )}
      </div>
    );
  }
}

export default withForwardedRef(WrappedInput);
