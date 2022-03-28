import axios from 'axios';

const { root, nonce } = window.assetManager;

const getFile = ID =>
  axios.get(`${root}wp/v2/media/${ID}`, {
    headers: {
      'X-WP-Nonce': nonce,
    },
    params: {
      context: 'edit',
    },
  });

export default getFile;
