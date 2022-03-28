import 'arrive';
import 'intersection-observer';

import { isObject } from 'lodash-es';
import '../styles/index.scss';

import CMP from '../modules/sourcepoint';
import indexExchange from '../modules/indexExchange';
import gpt from '../modules/gpt';
import permutive from '../modules/permutive';
import outbrain from '../modules/outbrain';
import nativo from '../modules/nativo';
import blueconic from '../modules/blueconic';
import setCookies from '../modules/setCookies';
import jwplayer from '../modules/jw-player';

// Initialize consent platform.
window.IDG.CMP = CMP();

// Add helper methods to window.
window.IDG.getDataLayer = () => {
  if (!window?.dataLayer || !isObject(window?.dataLayer[0])) {
    return {};
  }

  return window.dataLayer[0];
};

window.IDG.getItemFromDataLayer = (key = false) => {
  if (!key || !window?.dataLayer || !isObject(window?.dataLayer[0])) {
    return null;
  }

  return window.dataLayer[0][key];
};

window.IDG.setItemToDataLayer = (key, value) => {
  if (!window?.dataLayer || !isObject(window?.dataLayer[0])) {
    window.dataLayer = [];
    window.dataLayer.push({});
  }

  window.dataLayer[0][key] = value;
};

window.IDG.CMP.onConsentApplied(() => {
  // Load order is important.
  setCookies();
  indexExchange();
  gpt();
  permutive();
  outbrain();
  nativo();
  blueconic();
  jwplayer();
});
