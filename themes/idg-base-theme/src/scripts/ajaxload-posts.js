jQuery(function ajaxload($) {
  let currentPage = 1;

  $('.articleFeed-button .ajax-load').on('click', function ajaxloadClick(e) {
    e.preventDefault();

    const button = $(this);
    const buttonText = button[0].innerHTML;

    $.ajax({
      url: ajaxload_params.ajaxurl, //eslint-disable-line
      data: {
        action: 'ajaxload',
        page: currentPage,
        filters: button[0].dataset.filters,
        perpage: button[0].dataset.perpage,
        offset: button[0].dataset.offset,
        exclude: button[0].dataset.exclude,
        _ajaxnonce: ajaxload_params.nonce, //eslint-disable-line
      },
      type: 'POST',
      beforeSend: function beforeSend() {
        button.text('Loading...');
      },
      success: function success(data) {
        if (data) {
          // Changes button text back
          button.text(buttonText);

          // Inserts new posts.
          button.parentsUntil('.articleFeed').find('article:last-of-type').after(data);

          // Increase page count.
          currentPage += 1;

          // Checks for end of pages stopper.
          const endCheck = document.getElementById('end-of-posts');

          // Removes button if no more pages.
          if (endCheck) {
            button.parent().remove();
          }
        }
      },
    });
  });
});
