const device = () => {
  const width = window.innerWidth || document.body.clientWidth;
  const breakpoints = {
    superwide: 1460,
    desktop: 769,
    tablet: 569,
  };

  let type = 'mobile';

  Object.keys(breakpoints).forEach(breakpoint => {
    if (width >= breakpoints[breakpoint] && type === 'mobile') {
      type = breakpoint;
    }
  });

  return type;
};

export default device;
