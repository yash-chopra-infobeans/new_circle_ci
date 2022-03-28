export default function viewMorePrices() {
  document.addEventListener('DOMContentLoaded', () => {
    const priceComparisonBlocks = document.getElementsByClassName('wp-block-price-comparison');

    if (priceComparisonBlocks.length < 1) {
      return;
    }

    Array.from(priceComparisonBlocks).forEach(block => {
      const viewMoreButton = block.getElementsByClassName('price-comparison__view-more-button')[0];
      const hiddenRecordsDiv = block.getElementsByClassName(
        'price-comparison__hidden-records-wrapper',
      )[0];

      if (!hiddenRecordsDiv || !viewMoreButton) {
        return;
      }

      viewMoreButton.addEventListener('click', () => {
        hiddenRecordsDiv.classList.toggle('price-comparison__hidden-records-wrapper--is-open');
        const isOpen = hiddenRecordsDiv.classList.contains(
          'price-comparison__hidden-records-wrapper--is-open',
        );

        if (isOpen) {
          viewMoreButton.innerText = 'View fewer prices';
        } else {
          viewMoreButton.innerText = 'View more prices';
        }
      });
    });
  });
}
