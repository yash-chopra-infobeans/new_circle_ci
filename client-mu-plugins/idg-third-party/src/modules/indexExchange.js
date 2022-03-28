import loadScript from './loadScript';

const { IDG } = window;

let hasLoaded = false;

const indexExchange = () => {
  const id = IDG?.settings?.index_exchange?.config?.id;

  if (!id || hasLoaded) {
    return;
  }

  hasLoaded = true;

  loadScript(`https://js-sec.indexww.com/ht/p/${id}.js`, true);
};

export default indexExchange;
