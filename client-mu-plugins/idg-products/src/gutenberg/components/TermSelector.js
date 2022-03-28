import { debounce } from 'lodash-es';

const { FormTokenField } = wp.components;
const { dispatch, useSelect } = wp.data;
const { useState, useCallback } = wp.element;

const TermSelector = ({ onChange, taxonomy = '', create = false, ...props }) => {
  const [value, setValue] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [input, setInput] = useState('');

  const updateInput = useCallback(debounce(setInput, 250), []);

  const suggestions = useSelect(
    select => {
      return select('core').getEntityRecords('taxonomy', taxonomy, { search: input }) || [];
    },
    [input],
  );

  const createOrFindTerms = async terms => {
    return Promise.all(
      terms.map(async term => {
        if (term?.value) {
          return term;
        }

        const foundTerm = suggestions.find(m => m.name.toLowerCase() === term.toLowerCase());

        if (foundTerm) {
          return { value: foundTerm.name, id: foundTerm.id };
        }

        if (create) {
          setIsLoading(true);

          const { id, code, data } = await dispatch('core').saveEntityRecord('taxonomy', taxonomy, {
            name: term,
          });

          setIsLoading(false);

          if (code && code === 'term_exists') {
            return { value: term, id: data.term_id };
          }

          if (id) {
            return { value: term, id };
          }
        }

        return null;
      }),
    );
  };

  const onAdd = async terms => {
    const updatedTerms = await createOrFindTerms(terms);
    const newValue = updatedTerms.filter(x => x);
    setValue(newValue);
    onChange(newValue);
  };

  return (
    <FormTokenField
      value={value}
      disabled={isLoading}
      onInputChange={updateInput}
      suggestions={suggestions.map(x => x.name)}
      onChange={onAdd}
      {...props}
    />
  );
};

export default TermSelector;
