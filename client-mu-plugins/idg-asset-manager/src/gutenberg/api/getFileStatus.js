import axios from 'axios';

const { root, nonce } = window.assetManager;

const getFileStatus = ID =>
  axios.get(`${root}idg/v1/video/status/${ID}`, {
    headers: {
      'X-WP-Nonce': nonce,
    },
  });

export default getFileStatus;
