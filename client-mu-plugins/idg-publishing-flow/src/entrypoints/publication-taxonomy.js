import '../taxonomy/styles.scss';

const { domReady } = wp;

domReady(() => {
  const typeSelector = document.getElementById('type');

  typeSelector.addEventListener('change', event => {
    const parentSelector = document.getElementById('parent');
    const parentWrapper = document.querySelector('.term-parent-wrap');

    const businessUnitWrapper = document.querySelector('.business-unit-wrapper');
    const publicationWrapper = document.querySelector('.publication-wrapper');

    const selectedValue = event.target.value;

    if (selectedValue === 'business-unit') {
      parentSelector.value = '-1';
      parentWrapper.style.display = 'none';
      businessUnitWrapper.classList.add('display-section');
      publicationWrapper.classList.remove('display-section');
    } else {
      parentWrapper.style.display = 'block';
      businessUnitWrapper.classList.remove('display-section');
      publicationWrapper.classList.add('display-section');
    }
  });
});
