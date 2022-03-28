export default function newsletterSignUp() {
  document.addEventListener('load', () => {
    const script = document.getElementById('0.0370093555333746');
    const form = script.parentNode;
    const inputs = form.getElementsByTagName('INPUT');
    let submitCount = 0;
    let enableDoubleSubmit = false;

    for (let i = 0; i < inputs.length; i += 1) {
      const myInput = inputs[i];
      if (myInput.type === 'submit') {
        const container = myInput.parentNode;
        if (container.className.match(/enable-double-submit/)) {
          enableDoubleSubmit = true;
        }
      }
    }

    // to capture email value for ALC, per ATRA-385
    const email = document.getElementById('amf-input-email_505');
    const ALC = '';
    form.addEventListener('submit', function (evt) {
      if (submitCount >= 1 && !enableDoubleSubmit) {
        evt.preventDefault();
      }
      submitCount += 1;
      ALC.hashEmail(email);
    });
  });
}
