const elementInView = (element, offset = 0, threshold = 0) => {
  if (element.offsetParent === null) return false;

  const { top, right, bottom, left, width, height } = element.getBoundingClientRect();

  const intersection = {
    t: bottom,
    r: window.innerWidth - left,
    b: window.innerHeight - top,
    l: right,
  };

  const elementThreshold = {
    x: threshold * width,
    y: threshold * height,
  };

  return (
    intersection.t >= elementThreshold.y - offset &&
    intersection.r >= elementThreshold.x &&
    intersection.b >= elementThreshold.y - offset &&
    intersection.l >= elementThreshold.x
  );
};

export default elementInView;
