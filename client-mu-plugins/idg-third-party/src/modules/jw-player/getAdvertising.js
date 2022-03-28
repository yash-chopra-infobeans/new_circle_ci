import getAdSchedule from './getAdSchedule';
import getDeviceType from './getDeviceType';

const {
  adscheduleid = 'fQeHP23f',
  floating_player_adscheduleid: floatingPlayerScheduleId = 'fQeHP23f',
  sz = '640x480',
  ciu_szs = '300x250,728x90', // eslint-disable-line camelcase
  description_url = 'http://www.macworld.com', // eslint-disable-line camelcase
} = window?.IDG?.settings?.jw_player?.config || {};

const getAdScheduleId = id => {
  let adId = '';

  if (id === 'jwplayer--floatingVideo') {
    adId = adscheduleid;
  }

  if (id === 'jwplayer--featuredVideo') {
    adId = floatingPlayerScheduleId;
  }

  return adId;
};

const getAdvertising = ({ id, duration = 0 }) => {
  if (window?.IDG?.suppress_monetization?.jwplayer) {
    return {};
  }

  let playertype = 'articleEmbedPlayer';
  let pos = 'embed';

  if (id === 'jwplayer--floatingVideo') {
    playertype = 'bottomRightPlayer';
    pos = 'bottom_right';
  }

  if (id === 'jwplayer--featuredVideo') {
    playertype = 'galleryPlayer';
    pos = 'gallery';
  }

  const env = window.IDG.getItemFromDataLayer('environment');
  const pagetype = window.IDG.getItemFromDataLayer('pagetype');
  const url = window.IDG.getItemFromDataLayer('url');

  const custParams = {
    dlm: window?.googletag.pubads().getTargeting('dlm'),
    fr: window?.googletag.pubads().getTargeting('fr'),
    grm: window?.googletag.pubads().getTargeting('grm'),
    vw: window?.googletag.pubads().getTargeting('vw'),
    URL: url,
    articleId: window.IDG.getItemFromDataLayer('articleId'),
    blogId: window.IDG.getItemFromDataLayer('blogId'),
    categoryIds: window.IDG.getItemFromDataLayer('categoryIds'),
    categorySlugs: window.IDG.getItemFromDataLayer('categories'),
    channel: window.IDG.getItemFromDataLayer('channel'),
    env: window?.googletag.pubads().getTargeting('env'),
    goldenIds: window.IDG.getItemFromDataLayer('gtaxIdList'),
    pagetype,
    permutive: window?.googletag.pubads().getTargeting('permutive'),
    playertype,
    pos,
    positiondata: `${pagetype}_${getDeviceType()}_${pos}`,
    sponsored: window.IDG.getItemFromDataLayer('sponsorName') ? true : false, // eslint-disable-line  no-unneeded-ternary
    tagNames: window.IDG.getItemFromDataLayer('tags'),
    zone: window.IDG.getItemFromDataLayer('zone'),
    devsite: env !== 'wp_prod' && env !== 'wp_production',
  };

  const unitName = `${window.IDG.GPT.prefix}${window.IDG.GPT.ad_slot_name}`;

  // VAST ad tag URL parameters reference: https://support.google.com/admanager/table/9749596?hl=en
  const tagParams = {
    sz,
    iu: unitName,
    ciu_szs,
    impl: 's',
    gdfp_req: 1,
    env: 'vp',
    output: 'vast',
    unviewed_position_start: 1,
    description_url: encodeURI(description_url),
    url,
    correlator: window.IDG.getItemFromDataLayer('timestamp'),
  };

  return {
    client: 'googima',
    loadVideoTimeout: 60000,
    creativeTimeout: 60000,
    requestTimeout: 60000,
    adscheduleid: getAdScheduleId(id),
    schedule: getAdSchedule({ id, tagParams, custParams, duration }),
  };
};

export default getAdvertising;
