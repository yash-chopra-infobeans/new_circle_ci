import loadScript from './loadScript';
import onDocumentReady from './onDocumentReady';

const { IDG } = window;

let hasLoaded = false;

const outbrain = () => {
  if (window?.IDG?.suppress_monetization?.outbrain) {
    return;
  }

  const script = IDG?.settings?.outbrain?.config?.script;

  if (!script || hasLoaded) {
    return;
  }

  onDocumentReady(() => {
    hasLoaded = true;

    const outbrainElement = document.querySelector('.OUTBRAIN');

    if (!outbrainElement) {
      return;
    }

    const observer = new IntersectionObserver(
      changes => {
        changes.forEach(entry => {
          if (!entry.isIntersecting) {
            return;
          }

          loadScript(script, true);
          outbrainElement.classList.add('has-loaded');
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.5,
      },
    );

    observer.observe(outbrainElement);
  });
};

export default outbrain;
