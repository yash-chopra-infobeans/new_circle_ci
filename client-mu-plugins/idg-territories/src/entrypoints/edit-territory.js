import '../styles/edit-territory.scss';
import updateTerritoryTerm from '../modules/updateTerritoryTerm';

document.addEventListener('DOMContentLoaded', () => {
  const countrySelector = document.getElementById('country-selector');

  updateTerritoryTerm(countrySelector.value);

  countrySelector.addEventListener('change', event => updateTerritoryTerm(event.target.value));
});
