import loadScript from '../loadScript';
import trackPage from './page';
import trackProducts from './product';
import trackLinks from './links';

const { IDG } = window;

let hasLoaded = false;

const track = () => {
  // if script has already been loaded no need to call below fucntions to add event listeners.
  if (hasLoaded) {
    return;
  }

  // page level tracking
  trackPage();

  // permutive user

  // permutive product(s)
  trackProducts();

  // affiliate link tracking
  trackLinks();
};

const permutive = () => {
  const id = IDG?.settings?.permutive?.account?.workspace_id || '';

  if (!id) {
    return;
  }

  // If script has not been loaded then add script.
  if (!hasLoaded) {
    loadScript(`https://cdn.permutive.app/${id}-web.js`, true).then(() => {
      track();
      hasLoaded = true;
    });

    return;
  }

  track();
};

export default permutive;
