/**
 * @file
 * Collapsing Book Navigation Block behaviors.
 */

(function(Drupal) {
  "use strict";

  document.addEventListener('DOMContentLoaded', function(){
    /* On initial page load - expand active menu tree */
    setTimeout(() => {
      expandActiveMenu();

      for(let toggleIcon of document.querySelectorAll('.block-collapsing-book-navigation .toggle-icon')){

        toggleIcon.addEventListener('click', function(evt){
          evt.preventDefault();

          this.classList.toggle('menu-item--expanded');
          this.parentElement.getElementsByTagName('ul')[0].classList.toggle('show');
        });
      }
    }, 100);


  }, false);

  function expandActiveMenu(){
    var nid = drupalSettings.path.currentPath.replace(/node\//, "");
    var rootTrailElement = document.getElementById('menu-id--' + nid);

    /* Expand first list under active page */
    rootTrailElement.querySelector('.menu-link').classList.add('active');
    rootTrailElement.querySelector('.toggle-icon').classList.add('menu-item--expanded');
    rootTrailElement.querySelector('ul').classList.add('show');

    /* Traverse tree until at the top; select list elements along the way. */
    let parents = traverseActiveTrail(rootTrailElement);

    /* For each parent list, add the 'show' class to expand it. */
    for (var i = 0; i < parents.length; i++) {
      if (parents[i].nodeName == "UL") {
        parents[i].classList.add('show');
      } else {
        var icon = parents[i].querySelector('.toggle-icon');
            icon.classList.add('menu-item--expanded');
            icon.setAttribute("aria-expanded", true);
      }
    }
  }

  function traverseActiveTrail(element){
    let parents = [];
    let parentBlock = element.closest('.block-collapsing-book-navigation .content');

    while (element.parentElement != parentBlock) {
      element = element.parentElement;
      parents.push(element);
    }

    return parents;
  }
})(Drupal);
