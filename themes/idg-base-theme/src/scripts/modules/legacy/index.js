export default function legacy() {
  document.addEventListener('DOMContentLoaded', () => {
    const isLegacy = document.querySelectorAll('.single-post article.post-legacy');

    // Check it's a legacy post.
    if (isLegacy) {
      // Initialize colorbox.
      // jQuery('a.zoom').colorbox({ rel: 'gal' });
    }
  });
}
