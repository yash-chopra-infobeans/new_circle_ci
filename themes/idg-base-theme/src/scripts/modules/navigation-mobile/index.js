export default function mobileNav() {
  document.addEventListener('DOMContentLoaded', () => {
    const menuOpenButton = document.getElementById('mobileNav-open-button');
    const menuCloseButton = document.getElementById('mobileNav-close-button');

    // Make sure the buttons are available
    if (!menuOpenButton && !menuCloseButton) {
      return;
    }

    // When `mobileNav-open-button` is clicked adds `mobileNav--is-open` to body
    menuOpenButton.addEventListener('click', event => {
      event.preventDefault();
      document.body.classList.add('mobileNav--is-open');

      // Closes all other submenus on the page
      const elems = document.querySelectorAll('.menu-item-has-children');
      elems.forEach(elem => {
        elem.classList.remove('subMenu--is-open');
      });
      document.body.classList.remove('subMenu--is-open');
    });

    // When `mobileNav-close-button` is clicked removes `mobileNav--is-open` from body
    menuCloseButton.addEventListener('click', event => {
      event.preventDefault();
      document.body.classList.remove('mobileNav--is-open');
      document.body.classList.remove('subMenu--is-open');
    });
  });
}
