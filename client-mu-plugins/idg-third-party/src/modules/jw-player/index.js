import loadScript from '../loadScript';
import onDocumentReady from '../onDocumentReady';

import getAdvertising from './getAdvertising';
import getSetupConfig from './getSetupConfig';
import setPosition from './setPosition';

const DEFAULT_PLAYER_LIBRARY_ID = 'kAvvfxjt';

/**
 * Init a single jwPlayer.
 *
 * @param {HTMLElement} player - The player
 */
const initJwPlayer = player => {
  const { jwplayer, IDG } = window;
  const { id, dataset } = player;
  const { mediaId, title } = dataset;

  const config = getSetupConfig(player, id, mediaId, title);

  jwplayer(player).setup(config);

  const showAds = !IDG?.suppress_monetization?.jwplayer;

  // Reinit for ads as duration isn't defined on first load.
  if (showAds) {
    jwplayer().on('ready', () => {
      const duration = jwplayer(id).getDuration();
      const advertising = getAdvertising({ id, duration });

      const urlParams = new URLSearchParams(window.location.search); // eslint-disable-line
      const debug = urlParams.get('jwplayerDebug');

      if (debug) {
        console.log(`jwplayerConfig, player id: ${id}`, {
          ...config,
          advertising,
        });
      }

      jwplayer(player).setup({
        ...config,
        advertising,
      });
    });
  }
};

/**
 * Init all JWPlayers on the page.
 *
 * @param {array} jwplayers - The jwplayer elements,
 */
const initJwPlayers = (jwplayers = []) => {
  jwplayers.forEach(player => {
    initJwPlayer(player);
  });

  setPosition(document.querySelector('.primaryFooter'));
};

let hasLoaded = false;

const jwPlayers = () => {
  onDocumentReady(() => {
    const { IDG, googletag } = window;

    const playerLibraryId =
      IDG?.settings?.jw_player?.config?.player_library_id || DEFAULT_PLAYER_LIBRARY_ID;

    const jwplayers = document.querySelectorAll('.jwplayer');

    if (!jwplayers) {
      return;
    }

    if (hasLoaded) {
      return;
    }

    loadScript(`https://cdn.jwplayer.com/libraries/${playerLibraryId}.js`, true).then(() => {
      googletag.cmd.push(() => {
        initJwPlayers(jwplayers);
      });

      hasLoaded = true;
    });
  });
};

export default jwPlayers;
