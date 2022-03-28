import loadScript from './loadScript';

const { IDG } = window;

let hasLoaded = false;

const blueconic = () => {
  const id = IDG?.settings?.blueconic?.config?.script || '';

  if (hasLoaded || !id) {
    return;
  }

  loadScript(id, true);

  hasLoaded = true;
};

export default blueconic;
