/**
 * Handle updating a territory term fields when a country is selected.
 *
 * @param {string} countryCode - The iso 3166 1 alpha2 code for the country that is selected.
 */
const updateTerritoryTerm = countryCode => {
  const slugInput = document.getElementById('tag-slug') || document.getElementById('slug');

  const currencies = Array.from(document.querySelectorAll('.currency-selector-wrapper'));

  const currentCurrency = document.querySelector(`.currency-selector-wrapper.${countryCode}`);

  currencies.forEach(currency => {
    // eslint-disable-next-line no-param-reassign
    currency.style.display = 'none';
    currency.setAttribute('aria-hidden', true);
  });

  currentCurrency.style.display = 'block';
  currentCurrency.setAttribute('aria-hidden', false);

  slugInput.value = countryCode;

  const radioInput = currentCurrency.querySelector('input');

  radioInput.checked = true;
};

export default updateTerritoryTerm;
