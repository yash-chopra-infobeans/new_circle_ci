const getTag = params => {
  let paramsString = '';

  Object.keys(params).forEach(
    param => (paramsString += `${paramsString === '' ? '' : '&'}${param}=${params[param]}`), // eslint-disable-line no-return-assign
  );

  return `https://pubads.g.doubleclick.net/gampad/ads?${paramsString}`;
};

const getAdSchedule = ({ id, tagParams, custParams, duration = 0 }) => {
  const preRoll = [
    {
      tag: getTag({ ...tagParams, vpos: 'preroll' }),
      custParams,
      offset: 'pre',
    },
  ];

  // Only run prerolls on videos embedded in the article body or duration is less than 8 mins.
  if ((id !== 'jwplayer--floatingVideo' && id !== 'jwplayer--featuredVideo') || duration <= 480) {
    return [...preRoll];
  }

  let adEveryXSecs = 40;
  let maxMidRolls = duration / adEveryXSecs;

  const width = window.innerWidth || document.body.clientWidth;

  // If mobile set max midRolls to 4
  if (width <= 768) {
    maxMidRolls = 4;
    adEveryXSecs = 60;
  }

  const midRoll = [];
  // eslint-disable-next-line no-plusplus
  for (let index = 0; index < maxMidRolls; index++) {
    midRoll.push({
      tag: getTag({ ...tagParams, vpos: 'midroll' }),
      custParams,
      offset: `${parseInt(midRoll?.[index - 1]?.offset || 0, 10) + adEveryXSecs}`,
    });
  }

  return [...preRoll, ...midRoll];
};

export default getAdSchedule;
