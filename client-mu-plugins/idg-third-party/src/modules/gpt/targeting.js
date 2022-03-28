import { forIn } from 'lodash-es';

import device from './utils/device';

const targeting = () => {
  const { IDG, googletag } = window;

  if (IDG?.GPT?.targeting) {
    forIn(IDG.GPT.targeting, (value, key) => {
      googletag.pubads().setTargeting(key, value);
    });
  }

  const windowWidth = document.documentElement.clientWidth;

  googletag.pubads().setTargeting('inskin_yes', windowWidth >= 1330 ? 'true' : 'false');
  googletag.pubads().setTargeting('device', device());
};

export default targeting;
