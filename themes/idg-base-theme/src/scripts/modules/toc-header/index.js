export default function tocHeader() {
  const postHeadings = document.getElementsByClassName('toc');

  for (let i = 0; i < postHeadings.length; i += 1) {
    postHeadings[i].setAttribute('id', `toc-${i + 1}`);
  }

  // Create TOC Menu
  if (postHeadings.length > 0) {
    const postToc = document.getElementById('post-toc');
    let tocMenuHtml = '<div class="toc-wrapper"><ul><li class="toc-title">Table of Contents</li>';
    let i = 1;
    postHeadings.forEach(postHeading => {
      const postText = postHeading.innerText;
      tocMenuHtml = `${tocMenuHtml} <li class="toc-item"><a href="#toc-${i}">${postText}</a></li>`;
      i += 1;
    });

    if (postHeadings.length > 5) {
      postToc.innerHTML = `${tocMenuHtml}<li class='toc-show-more'>...</li></ul></div>`;
    } else {
      postToc.innerHTML = `${tocMenuHtml}</ul></div>`;
    }

    const showMore = document.querySelector('.toc-show-more');
    const tocItem = document.querySelectorAll('.toc-item');
    const tocItemArray = Array.from(tocItem);

    tocItemArray.slice(0, 5).forEach(item => {
      item.classList.add('is-open');
    });

    if (showMore) {
      showMore.addEventListener('click', show => {
        show.preventDefault();
        tocItem.forEach(item => {
          item.classList.add('is-open');
        });
        showMore.classList.add('hidden');
      });
    }

    const tocLinks = document.querySelectorAll('.toc-item a');

    if (tocLinks) {
      tocLinks.forEach(tocLink => {
        tocLink.addEventListener('click', function toc(e) {
          e.preventDefault();

          const tocID = tocLink.getAttribute('href');
          const tocJump = document.querySelector(tocID);
          const yOffset = -130;
          const y = tocJump.getBoundingClientRect().top + window.pageYOffset + yOffset;

          window.scrollTo({ top: y, behavior: 'smooth' });
        });
      });
    }
  }
}
