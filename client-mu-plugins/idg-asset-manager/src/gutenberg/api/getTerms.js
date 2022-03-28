import axios from 'axios';

const { root } = window.assetManager;

const getTerms = ({ taxonomy = 'asset_tag', params = {} }) =>
  axios.get(`${root}wp/v2/${taxonomy}`, {
    params,
  });

export default getTerms;
