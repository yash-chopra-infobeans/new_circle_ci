import axios from 'axios';

const { root, nonce } = window.assetManager;

const getFiles = ({ headers = {}, ...props }) =>
  axios.get(`${root}wp/v2/media`, {
    headers: {
      'X-WP-Nonce': nonce,
      ...headers,
    },
    ...props,
    params: {
      ...props.params,
      context: 'edit',
    },
  });

export default getFiles;
