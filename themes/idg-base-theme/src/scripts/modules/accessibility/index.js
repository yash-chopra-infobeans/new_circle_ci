export default function accessibility() {
  window.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      // Close all menus with escape key.
      // This includes dropdowns, site search & mobile nav.
      document.body.classList.remove('subMenu--is-open');
      document.body.classList.remove('mobileNav--is-open');
      document.body.classList.remove('siteSearch--is-open');
      const subMenuOpenButtons = document.querySelectorAll('.sub-menu-open-button');
      if (subMenuOpenButtons.length) {
        subMenuOpenButtons.forEach(subMenuOpenButton => {
          subMenuOpenButton.parentElement.classList.remove('subMenu--is-open');
        });
      }
    }
  });
}
