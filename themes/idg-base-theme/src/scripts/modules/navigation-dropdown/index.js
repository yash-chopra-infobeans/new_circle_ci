export default function dropdownNav() {
  document.addEventListener('DOMContentLoaded', () => {
    const subMenuOpenButtons = document.querySelectorAll('.menu-item-has-children');
    // Make sure there are buttons is available
    if (!subMenuOpenButtons.length) {
      return;
    }

    subMenuOpenButtons.forEach(subMenuOpenButton => {
      subMenuOpenButton.addEventListener('click', () => {
        if (subMenuOpenButton.classList.contains('subMenu--is-open')) {
          // If menu clicking on is open remove open class from all and hide overlay
          subMenuOpenButtons.forEach(elem => {
            elem.classList.remove('subMenu--is-open');
          });
          document.body.classList.remove('subMenu--is-open');
        } else {
          // Else remove open class from all and reapply to this and show overlay
          subMenuOpenButtons.forEach(elem => {
            elem.classList.remove('subMenu--is-open');
          });
          subMenuOpenButton.classList.add('subMenu--is-open');
          // If this is a dropdown menu show the site-overlay
          if (subMenuOpenButton.parentElement.classList.contains('is-dropdown')) {
            document.body.classList.add('subMenu--is-open');
          }
        }
      });
    });
  });
}
