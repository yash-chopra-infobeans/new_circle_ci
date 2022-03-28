import { isEmpty, isEqual } from 'lodash-es';
import CreatableSelect from 'react-select/async-creatable';

import api from '../api';

const { useEffect, useState } = wp.element;

const Tags = ({ value = [], onChange, create = false }) => {
  const [isLoading, setLoading] = useState(true);
  const [selectedTags, setSelectedTags] = useState([]);
  const getSelectedTags = () =>
    api.getTags({ include: value }).then(response => {
      const { data } = response;

      setSelectedTags(data.map(term => ({ label: term.name, value: term.id })));
    }, []);

  useEffect(() => {
    Promise.all([!isEmpty(value) && getSelectedTags()]).then(() => {
      setLoading(false);
    });
  }, [value]);

  const promiseOptions = inputValue =>
    new Promise(resolve => {
      api.getTags({ search: inputValue }).then(response => {
        const { data } = response;

        resolve(data.map(term => ({ label: term.name, value: term.id })));
      });
    });

  const handleChange = selectedOption => {
    const termIds = selectedOption
      ? selectedOption.reduce((carry, current) => {
          carry.push(current.value);

          return carry;
        }, [])
      : [];

    setSelectedTags(selectedOption);
    onChange(termIds);
  };

  const handleCreate = name => {
    setLoading(true);

    return api.createTag(name).then(response => {
      const { data } = response;

      const options = [...(selectedTags || []), { label: data.name, value: data.id }];

      handleChange(options);
      setSelectedTags(options);
      setLoading(false);
    });
  };

  const styles = {
    control: () => ({
      width: '100%',
      display: 'flex',
      boxSizing: 'border-box !important',
      color: '#555d66 !important',
      border: '1px solid #8d96a0 !important',
      borderRadius: '5px !important',
      minHeight: '41px !important',
    }),
  };

  return (
    <CreatableSelect
      className="selectableInput"
      styles={styles}
      isLoading={isLoading}
      isSearchable
      isMulti
      defaultOptions
      onChange={handleChange}
      isValidNewOption={(inputValue, selectValue, selectOptions) => {
        // if value is empty or less than 3 characters return false
        if (isEmpty(inputValue) || inputValue.length < 3) {
          return false;
        }

        // return false if the create prop is false
        if (!create) {
          return false;
        }

        // return false if term already exists for the inputValue
        if (
          selectOptions.filter(selectOption => isEqual(selectOption.label, inputValue)).length > 0
        ) {
          return false;
        }

        return true;
      }}
      onCreateOption={handleCreate}
      value={selectedTags}
      loadOptions={promiseOptions}
    />
  );
};

export default Tags;
