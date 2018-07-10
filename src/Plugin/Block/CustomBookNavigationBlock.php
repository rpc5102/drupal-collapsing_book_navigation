<?php
namespace Drupal\collapsing_book_navigation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\book\Plugin\Block\BookNavigationBlock;
use Drupal\Core\Cache\Cache;

  /**
   * Provides a 'Book navigation' block.
   *
   * @Block(
   *   id = "custom_book_navigation",
   *   admin_label = @Translation("Book navigation - Customized"),
   *   category = @Translation("Menus")
   * )
   */
class CustomBookNavigationBlock extends BookNavigationBlock {

  /**
   * {@inheritdoc}
   */
  private $depth = 1;
  private $markup = '';

  public function build() {
    $current_bid = 0;

    if ($node = $this->requestStack->getCurrentRequest()->get('node')) {
      $current_bid = empty($node->book['bid']) ? 0 : $node->book['bid'];
    }

    if ($this->configuration['block_mode'] == 'all pages') {
      foreach ($this->bookManager->getAllBooks() as $book_id => $book) {
          $this->buildBookTree($book);
      }
    } elseif ($current_bid) {
      $this->buildBookTree($book);
    }
    return array(
      '#markup' => $this->markup,
      '#cache' => array(
        'contexts' => $this->getCacheContexts(),
      )
    );
  }

    private function buildBookTree($book){
        $access = \Drupal::entityQuery('node')
                    ->condition('nid', $book['nid'], '=')
                    ->condition('status', NODE_PUBLISHED)
                    ->execute();

        if($access){
            $tree = $this->bookManager->bookTreeAllData($book['nid']);

            $this->markup .= '<ol id="book-'.$book['nid'].'" class="nav">';
            $this->bookTreeOutput($tree);
            $this->markup .= '</ol></ol>';
            $this->depth = 1;
        }
    }

    private function bookTreeOutput($node){
        foreach ($node as $key => $value) {
            $this->bookNodeOutput($node, $key);
        }
    }

    private function bookNodeOutput($node, $key){
        /* Check if current key is a link, otherwise, go deeper [below]. */
        if($key == "link"){
            $current_depth = $node[$key]['depth'];
            $has_children = $node[$key]['has_children'];
            $title = $node[$key]['title'];
            $nid = $node[$key]['nid'];
            $active = $node[$key]['in_active_trail'];

            /* Get link for current item. */
            $href = rtrim(base_path(),'/').\Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$nid);

            /* Check if we've moved up any levels; close tags if needed. */
            if($current_depth < $this->depth){
                $this->markup .= "</li>";
                $this->markup .= str_repeat("</ol>", $this->depth - $current_depth);
            }

            $this->markup .= "<li class='nav-item' data-id='".$nid."'>";

            /* If this node has children then we need to print the icon to expand/collapse list. */
            if($has_children){
                $this->markup .= "<a data-toggle='collapse' href='#nav-trail-".$nid."' class='toggle-icon'><i class='icon fa fa-fw fa-caret-right' aria-hidden='true'></i></a>";
            } else {
                $this->markup .= "<i class='far fa-fw fa-circle icon' aria-hidden='true' data-fa-transform='shrink-9'></i>";
            }

            $this->markup .= "<a href='".$href."' class='nav-link d-inline'>".$title."</a>";

            /* If this node has children we need to also put a list inside the current list element. */
            if ($has_children) {
                $this->markup .= "<ol id='nav-trail-".$nid."' class='nav-list collapse' >";
            } else {
                $this->markup .= "</li>";
            }

            $this->depth = $current_depth;
        } else {
            $this->bookTreeOutput($node[$key]);
        }
    }

    public function getCacheContexts() {
        return Cache::mergeContexts(parent::getCacheContexts(), ['route.book_navigation']);
    }

}