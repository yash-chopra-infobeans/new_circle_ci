import loadScript from './loadScript';
import onDocumentReady from './onDocumentReady';

const { IDG } = window;

let hasLoaded = false;

const nativo = () => {
  if (window?.IDG?.suppress_monetization?.nativo) {
    return;
  }

  const script = IDG?.settings?.nativo?.config?.script;

  if (!script || hasLoaded) {
    return;
  }

  onDocumentReady(() => {
    hasLoaded = true;
    loadScript(script, true);
  });
};

export default nativo;
