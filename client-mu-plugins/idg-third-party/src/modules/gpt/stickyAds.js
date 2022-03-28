const DEFAULT_STICKY_DURATION = 12;
const ADMIN_BAR_SELECTOR = '#wpadminbar';
const PRIMARY_NAV_SELECTOR = '#primaryNav';
const MAIN_SELECTOR = '#primary';
const BANNER_SELECTOR = '.ad-banner';
const RIGHT_RAIL_SELECTOR = '.ad-right-rail';
const SLIDE_ANIMATION_DURATION = 400;
const BANNER_SLOT_NAME = 'gpt-leaderboard';
const MAX_WIDTH = 728;

let hasLoaded = false;

/**
 * Handle any sticky behaviour for ads.
 *
 * @param {object} slot
 */
const stickyAds = () => {
  const isHome = document.body.classList.contains('home');
  const bannerAd = document.querySelector(BANNER_SELECTOR);

  if (isHome) {
    if (bannerAd) {
      bannerAd.classList.remove('is-sticky');
    }

    return;
  }

  if (hasLoaded) {
    return;
  }

  const rightRailAd = document.querySelector(RIGHT_RAIL_SELECTOR);

  if (!bannerAd && !rightRailAd) {
    return;
  }

  const width = window.innerWidth || document.body.clientWidth;

  if (width <= MAX_WIDTH) {
    bannerAd.classList.remove('is-sticky');
    return;
  }

  hasLoaded = true;

  bannerAd.classList.add('is-sticky');

  const adminBar = document.querySelector(ADMIN_BAR_SELECTOR);
  const primaryNav = document.querySelector(PRIMARY_NAV_SELECTOR);
  const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
  const primaryNavHeight = primaryNav ? primaryNav.offsetHeight : 0;
  const bannerHeight = bannerAd ? bannerAd.offsetHeight : 0;

  if (bannerAd) {
    // Banner ad should overlay IDG menu, therefore we only need to account for the admin bar.
    bannerAd.style.top = `${adminBarHeight}px`;
  }

  if (rightRailAd) {
    // Ensure the right rail ad is not overlayed by the banner ad.
    rightRailAd.style.top = `${adminBarHeight + bannerHeight + 15}px`;
  }

  // Retrieve the banner slot so we can access it's config.
  const bannerSlot = window.IDG.GPT.slots.find(x => x?.config?.name === BANNER_SLOT_NAME);

  // Banner is only stuck for a defined duration.
  setTimeout(() => {
    const bannerIsStuck =
      bannerAd && document.querySelector(MAIN_SELECTOR).getBoundingClientRect().top < 0;

    if (bannerIsStuck) {
      if (bannerAd) {
        bannerAd.style.transform = 'translateY(-100%)';
      }

      if (rightRailAd) {
        rightRailAd.style.transform = `translateY(-${bannerAd.offsetHeight - primaryNavHeight}px)`;
      }
    }

    // If the banner is stuck, it is animated up. We want to trigger styles after the animation
    // has finished.
    setTimeout(
      () => {
        if (bannerAd) {
          bannerAd.style.transform = 'translateY(0%)';
          bannerAd.classList.remove('is-sticky');
          bannerAd.style.top = '0px';
        }

        if (rightRailAd) {
          // Ensure right rail ad is stuck below IDG nav once banner is no longer sticky.
          rightRailAd.style.top = `${adminBarHeight + primaryNavHeight + 15}px`;
          rightRailAd.style.transition = 'none';
          rightRailAd.style.transform = 'translateY(0px)';
        }
      },
      bannerIsStuck ? SLIDE_ANIMATION_DURATION : 0,
    );
  }, Number(bannerSlot?.config?.sticky_duration || DEFAULT_STICKY_DURATION) * 1000);
};

export default stickyAds;
