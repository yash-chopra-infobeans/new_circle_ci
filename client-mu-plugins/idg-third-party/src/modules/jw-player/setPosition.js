const setPlayerPosition = () => {
  const windowHeight =
    window.innerHeight ||
    document.documentElement.clientHeight ||
    document.getElementsByTagName('body')[0];
  const elem = document.querySelector('.primaryFooter');
  const rect = elem.getBoundingClientRect();
  const position = windowHeight - rect.top;
  const playerWrapper = document.querySelector('.jwplayer.jw-flag-floating .jw-wrapper');

  if (!playerWrapper) {
    return;
  }

  playerWrapper.style.bottom = `${position}px`;
};

const removePlayerPosition = () => {
  const playerWrapper = document.querySelector('.jwplayer.jw-flag-floating .jw-wrapper');

  if (!playerWrapper) {
    return;
  }

  playerWrapper.style.removeProperty('bottom');
};

const observer = new IntersectionObserver(changes => {
  changes.forEach(entry => {
    if (!entry.isIntersecting) {
      removePlayerPosition();
      document.removeEventListener('scroll', setPlayerPosition);
      return;
    }

    document.addEventListener('scroll', setPlayerPosition);
  });
});

const setPosition = el => {
  if (!el) {
    return;
  }

  observer.observe(el);
};

export default setPosition;
