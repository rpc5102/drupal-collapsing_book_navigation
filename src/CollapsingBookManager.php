<?php

namespace Drupal\collapsing_book_navigation;

use Drupal\book\BookManager;

// Override BookManager service.

class CollapsingBookManager extends BookManager {
  
  /**
   * {@inheritdoc}
   */
  public function bookTreeAllData($bid, $link = NULL, $max_depth = NULL) {
    $tree = &drupal_static(__METHOD__, []);
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Use $nid as a flag for whether the data being loaded is for the whole
    // tree.
    $nid = isset($link['nid']) ? $link['nid'] : 0;
    // Generate a cache ID (cid) specific for this $bid, $link, $language, and
    // depth.
    $cid = 'book-links:' . $bid . ':all:' . $nid . ':' . $language_interface->getId() . ':' . (int) $max_depth;

    if (!isset($tree[$cid])) {
      // If the tree data was not in the static cache, build $tree_parameters.
      $tree_parameters = [
        'min_depth' => 1,
        'max_depth' => $max_depth,
      ];
      if ($nid) {
        $active_trail = $this->getActiveTrailIds($bid, $link);
        $tree_parameters['active_trail'] = $active_trail;
        $tree_parameters['active_trail'][] = $nid;
      }

      // Build the tree using the parameters; the resulting tree will be cached.
      $tree[$cid] = $this->bookTreeBuild($bid, $tree_parameters);
    }
	
    return $tree[$cid];
  }
}
