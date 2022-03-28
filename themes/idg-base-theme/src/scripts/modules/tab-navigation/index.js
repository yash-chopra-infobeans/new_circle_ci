const clearActiveLinks = links => {
  // eslint-disable-next-line no-plusplus
  for (let i = links.length - 1; i >= 0; i--) {
    const { parentNode } = links[i];
    parentNode.classList.remove('active');
  }
};

const setActiveLinks = links => {
  // eslint-disable-next-line no-plusplus
  for (let i = links.length - 1; i >= 0; i--) {
    const item = links[i];
    const { hash, href } = item;
    const { location } = window;

    if ((hash === location.hash && hash) || (href === location.href && href)) {
      item.parentNode.classList.add('active');
    }
  }
};

const tabOnClick = () => {
  const itemLinks = document.querySelectorAll('.tab-item a');
  clearActiveLinks(itemLinks);
  setActiveLinks(itemLinks);
};

const structureTabNavigation = () => {
  const tabContainer = document.querySelector('.tab-items');

  if (!tabContainer) {
    return;
  }

  const tabItems = tabContainer.cloneNode(true).childNodes;
  tabContainer.innerHTML = '';

  const tabGroup = document.querySelector('.tab-group');
  const groupItems = tabGroup.querySelector('.tab-group-items');
  groupItems.innerHTML = '';

  let hasExtra = false;

  const items = Array.from(tabItems).reverse();

  // eslint-disable-next-line no-plusplus
  for (let i = items.length - 1; i >= 0; i--) {
    const current = items[i];

    current.addEventListener('click', tabOnClick);

    if (hasExtra) {
      current.style.display = 'none';
      tabGroup.style.visibility = 'visible';
      tabContainer.appendChild(current);

      const groupCurrent = current.cloneNode(true);
      groupCurrent.style.display = 'block';
      groupItems.appendChild(groupCurrent);
    } else {
      current.style.display = 'block';
      current.classList.add('last-tab-item');
      tabContainer.appendChild(current);

      const { height: containerHeight = 0 } = tabContainer.getBoundingClientRect();
      const { height: itemHeight = 0 } = current.getBoundingClientRect();

      if (containerHeight > itemHeight) {
        i += 1;
        tabContainer.removeChild(tabContainer.lastChild);
        tabContainer.lastChild.classList.add('last-tab-item');
        hasExtra = true;
      }

      current.classList.remove('last-tab-item');
    }
  }
};

export default () => {
  const itemLinks = document.querySelectorAll('.tab-item a');

  setActiveLinks(itemLinks);

  document.addEventListener('DOMContentLoaded', structureTabNavigation);
  window.addEventListener('resize', structureTabNavigation);
};
