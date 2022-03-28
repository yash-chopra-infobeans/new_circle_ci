import { debounce } from 'lodash-es';

import stickyAds from './stickyAds';
import { addClass, removeClass } from './utils/class';
import elementInView from './utils/elementInView';

const refreshInViewSlots = () => {
  const slotsToRefresh = window.IDG.GPT.slots
    .filter(slot => {
      const ad = document.getElementById(slot.id);

      if (!ad) {
        return false;
      }

      if (ad.classList.contains('has-loaded')) {
        return true;
      }

      if (ad) {
        return elementInView(ad);
      }

      return false;
    })
    .map(slot => slot.googleslot);

  const { googletag } = window;

  googletag.cmd.push(() => {
    googletag.pubads().refresh(slotsToRefresh);
  });
};

const events = () => {
  const { googletag } = window;

  googletag.pubads().addEventListener('slotRequested', event => {
    addClass(event.slot.getSlotElementId(), 'is-requesting');
  });

  googletag.pubads().addEventListener('slotRenderEnded', event => {
    const id = event.slot.getSlotElementId();
    addClass(id, 'has-rendered');
    removeClass(id, 'is-requesting');
  });

  googletag.pubads().addEventListener('slotOnload', event => {
    const id = event.slot.getSlotElementId();
    addClass(id, 'has-loaded');

    if (id === 'gpt-leaderboard-1') {
      stickyAds();
    }
  });

  // Refresh ads on resize, but only if the window width has actually changed.
  let windowWidth = document.documentElement.clientWidth;

  const resizeAds = () => {
    const newWindowWidth = document.documentElement.clientWidth;

    if (newWindowWidth !== windowWidth) {
      windowWidth = newWindowWidth;

      refreshInViewSlots();
    }
  };

  window.addEventListener('resize', debounce(resizeAds, 500));
};

export default events;
