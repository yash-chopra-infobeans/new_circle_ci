export default function relatedSites() {
  const el = document.getElementById('footerSelect');

  if (!el) {
    return;
  }
  el.onchange = function goTo() {
    if (this.value !== '') {
      window.location.assign(encodeURI(this.value));
    }
  };
}
