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

      let toggleIcons = document.querySelectorAll('.block-collapsing-book-navigation .toggle-icon');

      for(let toggleIcon of toggleIcons){

        toggleIcon.addEventListener('click', function(evt){
          evt.preventDefault();

          this.classList.toggle('menu-item--expanded');
          this.parentElement.getElementsByTagName('ul')[0].classList.toggle('show');
        });
      }
    }, 100);

  }, false);

  function expandActiveMenu(){
    let nid = drupalSettings.path.currentPath.replace(/node\//, "");
    let rootTrailElement = document.getElementById('menu-id--' + nid);

    /* If trail is set expand the tree; otherwise no trail is active. */
    if(rootTrailElement){
      /* Expand first list under active page */
      rootTrailElement.querySelector('.menu-link').classList.add('active');

      let rootSubtree = rootTrailElement.querySelector('.toggle-icon');

      if(rootSubtree){
        rootSubtree.classList.add('menu-item--expanded');
        rootTrailElement.querySelector('ul').classList.add('show');
      }

      /* Traverse tree until at the top; select list elements along the way. */
      let parents = traverseActiveTrail(rootTrailElement);

      /* For each parent list, add the 'show' class to expand it. */
      for (let i = 0; i < parents.length; i++) {
        if (parents[i].nodeName == "UL") {
          parents[i].classList.add('show');
        } else {
          let icon = parents[i].querySelector('.toggle-icon');
              icon.classList.add('menu-item--expanded');
              icon.setAttribute("aria-expanded", true);
        }
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
