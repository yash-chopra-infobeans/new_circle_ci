export default function siteSearch() {
  document.addEventListener('DOMContentLoaded', () => {
    const menuOpenButton = document.getElementById('siteSearch-open-button');
    const menuCloseButton = document.getElementById('siteSearch-close-button');

    // Make sure the buttons are available
    if (!menuOpenButton && !menuCloseButton) {
      return;
    }

    // When `siteSearch-open-button` is clicked adds `siteSearch--is-open` to body
    menuOpenButton.addEventListener('click', event => {
      event.preventDefault();
      document.body.classList.add('siteSearch--is-open');

      // Closes all other submenus on the page
      const elems = document.querySelectorAll('.menu-item-has-children');
      elems.forEach(elem => {
        elem.classList.remove('subMenu--is-open');
      });
      document.body.classList.remove('subMenu--is-open');
    });

    // When `siteSearch-close-button` is clicked removes `siteSearch--is-open` from body
    menuCloseButton.addEventListener('click', event => {
      event.preventDefault();
      document.body.classList.remove('mobileNav--is-open');
      document.body.classList.remove('siteSearch--is-open');
    });
  });
}
