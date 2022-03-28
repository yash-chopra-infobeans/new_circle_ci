const getSetupConfig = (player, id, mediaId, title = false) => {
  const windowWidth = window.innerWidth || document.body.clientWidth;

  // All players - Default areguments for passed to JW Player setup function.
  let args = {
    advertising: {},
    playlist: `https://cdn.jwplayer.com/v2/media/${mediaId}`,
    image: `https://cdn.jwplayer.com/v2/media/${mediaId}/poster.jpg`, // poster image
    mediaid: mediaId,
    autostart: false,
    mute: false,
    floating: {
      mode: 'never',
    },
    ...(title ? { title } : {}),
    displaytitle: false,
    autoPause: {
      viewability: true,
      pauseAds: true,
    },
  };

  // Featured/Gallery player specific arguments, override defaults if required.
  if (id === 'jwplayer--featuredVideo') {
    args = {
      ...args,
      mute: true,
      floating: {
        mode: windowWidth <= 1207 ? 'never' : 'notVisible',
      },
      autostart: 'viewable',
    };
  }

  // Floating player specific arguments, override defaults if required.
  if (id === 'jwplayer--floatingVideo') {
    delete args.image;

    args = {
      ...args,
      playlist: `https://cdn.jwplayer.com/v2/playlists/${mediaId}?search=__CONTEXTUAL__`,
      mute: true,
      floating: {
        mode: windowWidth <= 1207 ? 'never' : 'notVisible',
      },
      autostart: 'viewable',
    };
  }

  return args;
};

export default getSetupConfig;
