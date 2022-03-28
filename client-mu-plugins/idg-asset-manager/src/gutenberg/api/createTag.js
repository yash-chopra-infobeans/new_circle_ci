import axios from 'axios';

const { root, nonce } = window.assetManager;

const createTag = name =>
  axios.post(
    `${root}wp/v2/asset_tag`,
    { name },
    {
      headers: {
        'X-WP-Nonce': nonce,
        'X-Requested-With': 'XMLHttpRequest',
      },
    },
  );

export default createTag;
