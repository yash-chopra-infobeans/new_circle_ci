import axios from 'axios';

const { root, nonce } = window.assetManager;

const deleteFile = ID =>
  axios.delete(`${root}wp/v2/media/${ID}?force=true`, {
    headers: {
      'X-WP-Nonce': nonce,
    },
  });

export default deleteFile;
