/**
 * @file
 * Collapsing Book Tree block behaviors.
 */

(function($, Drupal) {
  "use strict";
  var nid = drupalSettings.path.currentPath.replace(/node\//, "");

  $(document).ready(function() {
    var active = $('li[data-id="' + nid + '"]');

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

    $(active)
      .children(".nav-link")
      .first()
      .addClass("active");

    $(active)
      .children(".toggle-icon")
      .first()
      .toggleClass("fa-caret-down fa-caret-right");

    /* Expand first list under active page */
    $(active)
      .children("ul")
      .first()
      .addClass("show");

    /*
    $(".block-collapsing-book-navigation .nav-link")
      .mouseenter(function() {
        var icon = $(this)
          .prev(".icon")
          .first();
        $(icon).data("class", $(icon).attr("class"));
        $(icon).removeClass("fa-caret-right");
        $(icon).removeClass("fa-caret-down");
        $(icon).removeClass("fa-circle-o");
        $(icon).addClass("fa-chevron-right");
      })
      .mouseleave(function() {
        var icon = $(this)
          .prev(".icon")
          .first();
        $(icon).attr("class", $(icon).data("class"));
      });
      */

    /* Traverse tree until at the top; select list elements along the way. */
    var parents = $(active).parentsUntil("ul.nav");

    /* For each parent list, add the 'show' class to expand it. */
    for (var i = 0; i < parents.length; i++) {
      if ($(parents[i]).is("ul")) {
        $(parents[i]).addClass("show");
      } else {
        var icon = $(parents[i])
          .find(".toggle-icon")
          .first();
        $(icon).removeClass("fa-caret-right");
        $(icon).addClass("fa-caret-down");
      }
    }

    $(".block-collapsing-book-navigation .toggle-icon").on("click", function() {
      $(this).toggleClass("fa-caret-down");
      $(this).toggleClass("fa-caret-right");
    });
  });
})(jQuery, Drupal);
