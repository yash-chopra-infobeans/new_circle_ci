export default function priorityNav() {
  /* eslint-disable no-param-reassign */
  const calcWidth = (container, listItems, deductor) => {
    let listLength = 0;

    // Make sure the elements we need are available
    if (!container || !listItems) {
      return;
    }

    const contWidth = deductor
      ? container.getBoundingClientRect().width - deductor.getBoundingClientRect().width
      : container.getBoundingClientRect().width;

    // console.log('Cont:', contWidth);
    Array.from(listItems).forEach(item => {
      const itemMargin =
        parseFloat(window.getComputedStyle(item).paddingRight) +
        parseFloat(window.getComputedStyle(item).paddingLeft);
      listLength += item.getBoundingClientRect().width + itemMargin;
      if (listLength > contWidth) {
        // Moves item off screen so we can still get the width.
        item.style.position = 'absolute';
        item.style.top = '-9999px';
        item.style.visibility = 'hidden';
      } else {
        // Brings item back in.
        item.style.position = 'relative';
        item.style.visibility = 'visible';
        item.style.removeProperty('top');
      }
    });
  };
  /* eslint-enable no-param-reassign */

  const runPriority = () => {
    // Primary Menu
    const primaryContainer = document.querySelector('.primaryNav-menu-wrap');
    const primaryListItems = document.querySelectorAll('.primaryNav-menu > li');
    calcWidth(primaryContainer, primaryListItems);

    // Secondary Menu
    // const secondaryContainer = document.querySelector('.secondaryNav-wrap');
    // const secondaryDeductor = document.querySelector('.secondaryNav-social-wrap');
    // const secondaryListItems = document.querySelectorAll('.secondaryNav-menu > li');
    // calcWidth(secondaryContainer, secondaryListItems, secondaryDeductor);
  };

  document.addEventListener('DOMContentLoaded', runPriority);
  window.addEventListener('resize', runPriority);
}
