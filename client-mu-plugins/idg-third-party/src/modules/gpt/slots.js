/* eslint-disable */
import { isArray } from 'lodash-es';

import { extractSizes } from './utils/sizes';

const SLOT_PREFIX = window.IDG?.settings?.gpt?.config.prefix || '';
const SLOT_NAME = window.IDG?.GPT?.ad_slot_name || '';
const IAS_PUB_ID = window.IDG?.settings?.ias?.account?.pub_id || '';

export const displayAds = slot => {
  if (!slot) {
    return;
  }

  const ad = document.querySelector(`#${slot.id}`);

  if (!ad) {
    return;
  }

  const { googletag } = window;

  googletag.display(slot.id);

  // Out of page slots should load straight away.
  if (ad?.dataset?.ofp === 'true') {
    googletag.pubads().refresh([slot.googleslot]);
    return;
  }

  let refreshInterval = null;

  const observer = new IntersectionObserver(
    changes => {
      changes.forEach(entry => {
        if (!entry.isIntersecting) {
          if (refreshInterval) {
            clearInterval(refreshInterval);
          }

          return;
        }

        if (slot?.config?.replace_interval) {
          googletag.pubads().refresh([slot.googleslot]);
          // eslint-disable-next-line no-use-before-define
          replaceSlot(slot);
          observer.unobserve(entry.target);
          return;
        }

        if (!ad.classList.contains('has-loaded')) {
          googletag.pubads().refresh([slot.googleslot]);
        }

        if (!slot?.config?.refresh_interval) {
          observer.unobserve(entry.target);
          return;
        }

        let refreshAmount = slot?.config?.refresh_amount;

        refreshInterval = setInterval(() => {
          if (refreshAmount && Number(refreshAmount) <= 0 || !refreshAmount) {
            clearInterval(refreshInterval);
            observer.unobserve(entry.target);
            return;
          }

          // Refresh ad and reduce refresh amount if tab is viewable.
          if (document.hidden === false) {
            if (refreshAmount) {
              refreshAmount = Number(refreshAmount) - 1;
            }

            googletag.pubads().refresh([slot.googleslot]);
          }
        }, Number(slot?.config?.refresh_interval) * 1000);
      });
    },
    {
      threshold: window?.IDG?.settings?.gpt?.config?.threshold || 0.7,
    },
  );

  observer.observe(ad);
};

/**
 * Render a single slot
 *
 * @param {object} slot - IDG.GPT.slots[<index>]
 */
export const renderSlot = slot => {
  if (!slot) {
    return;
  }

  const ad = document.querySelector(`#${slot.id}`);

  if (!ad) {
    return;
  }

  const { googletag } = window;

  // Set up IAS pet.js
  let { __iasPET = {} } = window;
  __iasPET.queue = __iasPET?.queue || [];
  __iasPET.pubId = IAS_PUB_ID; // your account manager provides this ID

  const requestAds = () => displayAds(slot);

  // this is the maximum amount of time in milliseconds to wait
  // for a PET response before requesting ads without PET data.
  // IAS recommends starting at 2 seconds
  // when testing and adjusting downwards as appropriate.
  // remember to replace 'requestAds' below with the
  // function you use to request ads from DFP.
  const IASPET_TIMEOUT = 2000;
  const __iasPETTimeoutRequestAds = setTimeout(requestAds, IASPET_TIMEOUT);

  // this function is called when a PET response is received. it
  // sets the targeting data for DFP and request ads
  // remember to replace requestAds() with the function you use for requesting
  // ads from DFP
  const iasDataHandler = adSlotData => {
    clearTimeout(__iasPETTimeoutRequestAds);
    __iasPET.setTargetingForGPT();
    requestAds();
  };

  googletag.cmd.push(function () {
    // read the currently defined GPT ad slots for sending to the PET endpoint
    // defined all GPT ad slots before calling PET
    const gptSlots = [slot.googleslot];
    let iasPETSlots = [];
    for (let i = 0; i < gptSlots.length; i++) {
      const sizes = gptSlots[i].getSizes().map(function (size) {
        if (size.getWidth && size.getHeight) return [size.getWidth(), size.getHeight()];
        else return [1, 1];
      });
      iasPETSlots.push({
        adSlotId: gptSlots[i].getSlotElementId(),
        //size: can either be a single size (for example, [728, 90])
        // or an array of sizes (for example, [[728, 90], [970, 90]])
        size: sizes,
        adUnitPath: gptSlots[i].getAdUnitPath(),
      });
    }
    // make the request to PET. if your page makes multiple ad requests to DFP
    // (for example, lazily loaded ads, infinite scrolling pages, etc.), make
    // a request to PET before every request to DFP
    __iasPET.queue.push({
      adSlots: iasPETSlots,
      dataHandler: iasDataHandler,
    });
  });
};

/**
 * Define ad slot, slot sizes and pos targeting for a single slot.
 *
 * @param {array<object>} slots
 */
export const createSlot = (config = null, refresh = false) => {
  if (!config) {
    return;
  }

  if (config?.disabled) {
    return;
  }

  const selector = `[data-ad-template=${config.template}]`;

  // Not a native API - https://github.com/uzairfarooq/arrive. Used to cover all cases
  // including ad elements added to the DOM after page load.
  document.body.arrive(selector, { existing: true }, ad => {
    const index = window.IDG.GPT.slots.filter(x => x.config.name === config.name).length;
    const id = `${config.name}-${index + 1}`;

    // eslint-disable-next-line no-param-reassign
    ad.id = id;

    const sizeMap = window.googletag.sizeMapping();

    const responsiveSizeDefinitions = config?.size_definitions || [];

    responsiveSizeDefinitions.forEach(({ breakpoint, sizes }) => {
      sizeMap.addSize([parseInt(breakpoint, 10), 100], sizes ? extractSizes(sizes) : []);
    });

    sizeMap.addSize([0, 0], []);

    const googleslot = window.googletag.defineSlot(
      `${SLOT_PREFIX}${SLOT_NAME}`,
      extractSizes(config.size),
      id,
    );

    const mappedSizes = sizeMap.build();

    if (responsiveSizeDefinitions && mappedSizes) {
      googleslot.defineSizeMapping(mappedSizes);
    }

    if (config?.pos) {
      // Count is replaced by the number of the same ad units on the page.
      googleslot.setTargeting('pos', config?.pos.replaceAll('{{count}}', index + 1));
    }

    if (config?.section) {
      // Count is incremented every time an ad is replaced using replaceSlot
      googleslot.setTargeting(
        'section',
        config?.section.replaceAll('{{count}}', config?.replace_count || 1),
      );
    }

    googleslot.setCollapseEmptyDiv(true);

    googleslot.addService(window.googletag.pubads());

    const newSlot = {
      id,
      googleslot,
      config,
    };

    window.IDG.GPT.slots.push(newSlot);

    renderSlot(newSlot, refresh);
  });
};

/**
 * Replace ad logic.
 *
 * @param {object} slot
 */
export const replaceSlot = slot => {
  const interval = slot?.config?.replace_interval || 20;
  const replaceAmount = slot?.config?.replace_amount || 5;
  const replaceCount = slot?.config?.replace_count;

  const count = replaceCount ? replaceCount + 1 : 2;

  setTimeout(() => {
    const { googletag } = window;

    const ad = document.querySelector(`#${slot.id}`);
    ad.classList.remove('has-loaded');
    ad.classList.remove('has-rendered');

    googletag.destroySlots([slot.googleslot]);

    createSlot({
      ...slot.config,
      replace_interval: count >= Number(replaceAmount) ? false : interval,
      replace_count: count,
    });
  }, Number(interval) * 1000);
};

/**
 * Define slots.
 *
 * @param {array} slots
 */
export const createSlots = (slots = []) => {
  if (!isArray(slots)) {
    return;
  }

  slots.forEach(createSlot);
};
