import { isEmpty } from 'lodash-es';

const { TextControl, CheckboxControl } = wp.components;
const { useEffect, useState } = wp.element;

const TermSelector = ({
  value = [],
  options = [],
  search = false,
  searchPlaceholder = '',
  searchLabel = '',
  onChange,
  onSearchChange,
  filter = false,
}) => {
  const [selectOptions, setOptions] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    if (!isEmpty(searchTerm) && filter) {
      setOptions(
        options.filter(option => option.label.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1),
      );
    } else {
      setOptions(options);
    }
  }, [searchTerm, options]);

  return (
    <div className="termSelector">
      {search && (
        <TextControl
          label={searchLabel}
          placeholder={searchPlaceholder}
          value={searchTerm}
          onChange={s => {
            if (onSearchChange) {
              onSearchChange(s);
            }

            setSearchTerm(s);
          }}
        />
      )}
      <div className="termSelector-options">
        {selectOptions.map(selectOption => (
          <CheckboxControl
            heading={selectOption?.heading}
            label={selectOption.label}
            help={selectOption?.help}
            checked={value.includes(selectOption.value)}
            onChange={() => {
              const newValue = value.includes(selectOption.value)
                ? value.filter(term => term !== selectOption.value)
                : [...value, selectOption.value];

              onChange(newValue);
            }}
          />
        ))}
      </div>
    </div>
  );
};

export default TermSelector;
