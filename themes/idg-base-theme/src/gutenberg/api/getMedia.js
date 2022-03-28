import axios from 'axios';

const { root, nonce } = window.baseTheme;

const getMedia = ID =>
  axios.get(`${root}wp/v2/media/${ID}`, {
    headers: {
      'X-WP-Nonce': nonce,
    },
  });

export default getMedia;
