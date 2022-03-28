export const addClass = (id, className) => {
  const element = document.querySelector(`#${id}`);

  if (element) {
    element.classList.add(className);
  }
};

export const removeClass = (id, className) => {
  const element = document.querySelector(`#${id}`);

  if (element) {
    element.classList.remove(className);
  }
};
