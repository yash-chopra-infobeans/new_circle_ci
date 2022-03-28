import api from '../api';
import TermSelector from './TermSelector';

const { useEffect, useState } = wp.element;

const TermOptions = ({
  search = false,
  searchPlaceholder = '',
  taxonomy = '',
  value,
  onChange,
}) => {
  const [terms, setTerms] = useState([]);

  const getTerms = (params = {}) =>
    api.getTerms({ taxonomy, params }).then(response => {
      const { data } = response;

      setTerms(
        data.map(term => ({
          label: term.name,
          value: term.id,
        })),
      );
    });

  useEffect(() => {
    getTerms();
  }, []);

  return (
    <TermSelector
      search={search}
      searchPlaceholder={searchPlaceholder}
      onSearchChange={searchTerm => getTerms({ search: searchTerm })}
      options={terms}
      value={value}
      onChange={onChange}
    />
  );
};

export default TermOptions;
