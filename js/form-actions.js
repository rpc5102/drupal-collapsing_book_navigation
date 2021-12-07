Drupal.behaviors.selectAllFormItems = {
  attach: function (context, settings) {
    // Attach a click listener to the select all button
    document.querySelectorAll('[id^=edit-settings-books-displayed-select-all-books]').forEach((btn) => {
      btn.addEventListener('click', function(evt) {
        evt.preventDefault();
        btn.parentElement.querySelectorAll('[id^=edit-settings-books-displayed-selection] input[type="checkbox"]').forEach(book => {
          book.checked = true;
        });
      }, false)
    });

    // Attach a click listener to the deselect all button
    document.querySelectorAll('[id^=edit-settings-books-displayed-deselect-all-books]').forEach((btn) => {
      btn.addEventListener('click', function(evt) {
        evt.preventDefault();
        btn.parentElement.querySelectorAll('[id^=edit-settings-books-displayed-selection] input[type="checkbox"]').forEach(book => {
          book.checked = false;
        });
      }, false)
    });
  }
};
