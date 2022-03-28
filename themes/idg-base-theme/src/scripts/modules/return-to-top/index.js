export default function returnToTop() {
  const button = document.querySelector('.primaryFooter-return-top svg');
  const mobileButton = document.querySelector('.footer-base-child.return-top-mobile svg');

  if (!button || !mobileButton) {
    return;
  }

  button.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });

  mobileButton.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });
}
