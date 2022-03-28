import { isEmpty } from 'lodash';

const getCookie = name => {
  const cookies = Object.assign(
    {},
    ...document.cookie.split('; ').map(cookie => {
      const cname = cookie.split('=')[0];
      const value = cookie.split('=')[1];

      return { [cname]: value };
    }),
  );

  return cookies?.[name] || '';
};

const setCookies = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const huid = urlParams.get('huid');

  if (!isEmpty(huid)) {
    const expirtyDate = new Date();
    expirtyDate.setFullYear(expirtyDate.getFullYear() + 10);
    document.cookie = `arenaId=${huid}; expires=${expirtyDate.toUTCString()}`;
  }

  window.IDG.setItemToDataLayer('arenaId', getCookie('arenaId'));
};

export default setCookies;
