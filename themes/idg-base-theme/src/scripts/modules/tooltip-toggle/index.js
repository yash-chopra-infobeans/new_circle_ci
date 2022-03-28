export default function toggleTooltip() {
  const allTooltips = document.querySelectorAll('.tooltip-learn-more');
  const allTooltipsClose = document.querySelectorAll('.tooltip-close');

  if (!allTooltips.length || !allTooltipsClose.length) {
    return;
  }

  allTooltips.forEach(toolTip => {
    toolTip.addEventListener('click', function tog(e) {
      e.preventDefault();
      if (this.nextElementSibling.classList.contains('is-open')) {
        this.nextElementSibling.classList.remove('is-open');
      } else {
        this.nextElementSibling.classList.add('is-open');
      }
    });
  });

  allTooltipsClose.forEach(toolTip => {
    toolTip.addEventListener('click', function close(e) {
      e.preventDefault();
      this.closest('.tooltip-box').classList.remove('is-open');
    });
  });
}
