import axios from 'axios';

const { root, nonce } = window.assetManager;

const getUsers = ({ params = {} }) =>
  axios.get(`${root}wp/v2/users`, {
    params,
    headers: {
      'X-WP-Nonce': nonce,
    },
  });

export default getUsers;
