import { isEmpty, isArray } from 'lodash-es';

import onDocumentReady from '../onDocumentReady';
import loadScript from '../loadScript';

import events from './events';
import { createSlots } from './slots';
import targeting from './targeting';

/**
 * Load GPT.
 */
const load = () => {
  if (!window.googletag) {
    window.googletag = {};
    window.googletag.cmd = [];
  }

  const { IDG } = window;

  const hasLoaded = isArray(IDG?.GPT?.slots);

  if (!hasLoaded) {
    loadScript(`${document.location.protocol}//securepubads.g.doubleclick.net/tag/js/gpt.js`, true);
    window.IDG.GPT.slots = [];
  }

  return hasLoaded;
};

/**
 * Initialize gpt logic.
 */
const gpt = () => {
  const hasLoaded = load();

  if (hasLoaded) {
    return;
  }

  const { IDG, googletag } = window;

  const slotsConfig = IDG?.settings?.gpt?.config?.slots;

  if (isEmpty(slotsConfig)) {
    return;
  }

  googletag.cmd.push(() => {
    googletag.pubads().disableInitialLoad();
    googletag.enableServices();

    targeting();

    onDocumentReady(() => {
      createSlots(slotsConfig);
      events();
    });
  });
};

export default gpt;
