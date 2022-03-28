import { I18N_DOMAIN } from '../../settings';
import api from '../api';
import TermSelector from './TermSelector';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { currentUser } = window.assetManager;

const Useroptions = ({ search = false, searchPlaceholder = '', value, onChange }) => {
  const [terms, setTerms] = useState([]);

  const getUsers = (params = {}) =>
    api.getUsers({ params }).then(response => {
      const { data } = response;

      const users = data.map(user => ({
        label: user.id === currentUser.ID ? __('Me', I18N_DOMAIN) : user.name,
        value: user.id,
      }));

      users.forEach((user, i) => {
        if (user.value === currentUser.ID) {
          users.splice(i, 1);
          users.unshift(user);
        }
      });

      setTerms(users);
    });

  useEffect(() => {
    getUsers();
  }, []);

  return (
    <TermSelector
      search={search}
      searchPlaceholder={searchPlaceholder}
      onSearchChange={searchTerm => getUsers({ search: searchTerm })}
      options={terms}
      value={value}
      onChange={onChange}
    />
  );
};

export default Useroptions;
