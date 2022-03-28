import axios from 'axios';

const { root, nonce } = window.assetManager;

const updateFile = (ID, data) =>
  axios.post(`${root}wp/v2/media/${ID}`, data, {
    headers: {
      'X-WP-Nonce': nonce,
    },
  });

export default updateFile;
