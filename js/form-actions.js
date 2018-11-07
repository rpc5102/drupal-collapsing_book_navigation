Drupal.behaviors.selectAllFormItems = {
  attach: function (context, settings) {
    // Attach a click listener to the select all button
    document.getElementById('edit-settings-books-displayed-select-all-books').addEventListener('click', function(evt) {
        evt.preventDefault();
        var books = document.getElementById('edit-settings-books-displayed-selection--wrapper').querySelectorAll('input[type="checkbox"]');
        for (const book in books) {
          books[book].checked = true;
        }
    }, false);
    // Attach a click listener to the deselect all button
    document.getElementById('edit-settings-books-displayed-deselect-all-books').addEventListener('click', function(evt) {
      evt.preventDefault();
      var books = document.getElementById('edit-settings-books-displayed-selection--wrapper').querySelectorAll('input[type="checkbox"]');
      for (const book in books) {
        books[book].checked = false;
      }
    }, false);
  }
};