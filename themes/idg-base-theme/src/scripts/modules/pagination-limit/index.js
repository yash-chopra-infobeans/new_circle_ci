export default function paginationLimit() {
  const pag = document.querySelectorAll('.post-page-numbers');
  const pagArray = Array.from(pag);
  const arrayLength = pagArray.length;
  const currentPage = document.querySelector('.post-page-numbers.current');

  if (!currentPage) {
    return;
  }

  const currentPageInt = Number(currentPage.innerHTML);

  currentPage.classList.add('show');
  pagArray[0].classList.add('arrow');
  pagArray[arrayLength - 1].classList.add('arrow');

  if (arrayLength > 5) {
    pagArray[arrayLength - 1].previousElementSibling.classList.add('show');
  }

  if (pagArray[currentPageInt + 1]) {
    pagArray[currentPageInt + 1].classList.add('show');
  }
  if (pagArray[currentPageInt + 2]) {
    pagArray[currentPageInt + 2].classList.add('show');
  }

  if (pagArray[currentPageInt - 1]) {
    pagArray[currentPageInt - 1].classList.add('show');
  }

  if (pagArray[currentPageInt - 2]) {
    pagArray[currentPageInt - 2].classList.add('show');
  }

  if (!pagArray[currentPageInt + 3] && pagArray[currentPageInt - 3]) {
    pagArray[currentPageInt - 3].classList.add('show');
  }

  if (!pagArray[currentPageInt + 2] && pagArray[currentPageInt - 4]) {
    pagArray[currentPageInt - 4].classList.add('show');
  }

  if (!pagArray[currentPageInt - 2] && pagArray[currentPageInt + 4]) {
    pagArray[currentPageInt + 4].classList.add('show');
  }
  if (!pagArray[currentPageInt - 3] && pagArray[currentPageInt + 3]) {
    pagArray[currentPageInt + 3].classList.add('show');
  }
}
