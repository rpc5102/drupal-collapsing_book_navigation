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
    let activeMenuItem = document.querySelector('.book-block-menu .active');
   
    if(activeMenuItem){
    let rootTrailElement = activeMenuItem.closest('.menu-root');

    /* If trail is set expand the tree; otherwise no trail is active. */
    if(rootTrailElement){
      /* Expand first list under active page */
      if(activeMenuItem.previousElementSibling && activeMenuItem.previousElementSibling.tagName == "A") {
        activeMenuItem.previousElementSibling.classList.add('menu-item--expanded');
        activeMenuItem.nextElementSibling.classList.add('show');
      }
      let rootSubtree = rootTrailElement.querySelector('.toggle-icon');

      if(rootSubtree){
        rootSubtree.classList.add('menu-item--expanded');
        rootTrailElement.querySelector('ul').classList.add('show');
      }

      /* Traverse tree until at the top; select list elements along the way. */
      let parents = traverseActiveTrail(activeMenuItem, '.menu-root');

      /* For each parent list, add the 'show' class to expand it. */
      for (let i = 0; i < parents.length; i++) {
        if (parents[i].nodeName == "UL") {
          parents[i].classList.add('show');
        } else if(parents[i].nodeName == "LI") {
          let icon = parents[i].querySelector('.toggle-icon');
          if(icon) {
            icon.classList.add('menu-item--expanded');
            icon.setAttribute("aria-expanded", true);
          }  
        }
      }
    }
    }
  }

  function traverseActiveTrail(el, selector, filter) {
    const result = [];
    const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;
  
    // match start from parent
    el = el.parentElement;
    while (el && !matchesSelector.call(el, selector)) {
      if (!filter) {
        result.push(el);
      } else {
        if (matchesSelector.call(el, filter)) {
          result.push(el);
        }
      }
      el = el.parentElement;
    }
    return result;
  }

})(Drupal);
