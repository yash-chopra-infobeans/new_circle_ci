import axios from 'axios';

const { root } = window.assetManager;

const getTags = (params = {}) =>
  axios.get(`${root}wp/v2/asset_tag`, {
    params,
  });

export default getTags;
