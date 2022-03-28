export default function overlaySite() {
  document.addEventListener('DOMContentLoaded', () => {
    const siteOverlay = document.getElementById('site-overlay');

    // Make sure the buttons are available
    if (!siteOverlay) {
      return;
    }

    // When `site-overlay` is clicked hide overlay and close all menus
    siteOverlay.addEventListener('click', () => {
      document.body.classList.remove('mobileNav--is-open');
      document.body.classList.remove('subMenu--is-open');
      document.body.classList.remove('siteSearch--is-open');

      const elems = document.querySelectorAll('.menu-item-has-children');
      elems.forEach(elem => {
        elem.classList.remove('subMenu--is-open');
      });
    });
  });
}
