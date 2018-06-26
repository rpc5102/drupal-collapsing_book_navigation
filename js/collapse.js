/**
 * @file
 * Collapsing Book Tree block behaviors.
 */

(function($, Drupal) {
  "use strict";

  document.addEventListener('DOMContentLoaded', function () {

    /* Keyboard navigation
    $(".block-collapsing-book-navigation").on("keydown", function(e) {
      // keyCodes = [38, 40, 37, 39 : up, down, left, right]
      var key = e.which;
      var inFocus = $(document.activeElement);

      // down
      if (key == 40) {

      }
    });
    */

    /* @TODO: Count number of tree items and listen for how many get rendered by FontAwesome; trigger when done with active item */
    setTimeout(function() {
      expand_active_tree();  

      $(".block-collapsing-book-navigation .toggle-icon").on("click", function() {
        $(this)
          .find(".svg-inline--fa")
          .toggleClass("fa-caret-down")
          .toggleClass("fa-caret-right");
      });
    }, 200); 
  });

  function expand_active_tree(){
    var nid = drupalSettings.path.currentPath.replace(/node\//, "");
    var active = document.querySelectorAll('li[data-id="' + nid + '"]');

    $(active)
      .children(".nav-link")
      .first()
      .addClass("active");

    $(active)
      .children(".toggle-icon")
      .first()
      .find(".svg-inline--fa")
      .toggleClass("fa-caret-down");

    /* Expand first list under active page */
    $(active)
      .children("ol")
      .first()
      .addClass("show");

    /* Traverse tree until at the top; select list elements along the way. */
    var parents = $(active).parentsUntil("ol.nav");

    /* For each parent list, add the 'show' class to expand it. */
    for (var i = 0; i < parents.length; i++) {
      if ($(parents[i]).is("ol")) {
        $(parents[i]).addClass("show");
      } else {
        var icon = $(parents[i])
          .find(".toggle-icon")
          .first();

        $(icon)
          .find(".svg-inline--fa")
          .toggleClass("fa-caret-down")
          .toggleClass("fa-caret-right");
      }
    } 
  }
})(jQuery, Drupal);
