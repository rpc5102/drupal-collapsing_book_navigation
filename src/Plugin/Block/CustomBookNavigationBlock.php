<?php
namespace Drupal\collapsing_book_navigation\Plugin\Block;

use Drupal\book\BookManagerInterface;
use Drupal\book\Plugin\Block\BookNavigationBlock;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SortArray;

/* FOR TESTING ONLY */
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

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

   /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_mode' => "all pages",
      'books_displayed' => "all books",
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['#attached']['library'][] = 'collapsing_book_navigation/form-actions';

    ## Form Part 1 - Page Selection ##
    $options = [
      'all pages' => $this->t('Show block on all pages'),
      'book pages' => $this->t('Show block only on book pages'),
    ];

    $form['book_block_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Book navigation block display'),
      '#options' => $options,
      '#default_value' => $this->configuration['block_mode'],
      '#description' => $this->t("If <em>Show block on all pages</em> is selected, the block will contain the automatically generated menus for all of the site's books. If <em>Show block only on book pages</em> is selected, the block will contain only the one menu corresponding to the current page's book. In this case, if the current page is not in a book, no block will be displayed. The <em>Page specific visibility settings</em> or other visibility settings can be used in addition to selectively display this block."),
    ];

    ## Form Part 2 - Book Selection ##
    # @todo: add a way to handle lots of list choices
    
    unset($options);

    /* Build a list of books by weights */
    $books = $this->bookManager->getAllBooks();

    uasort($books, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

    foreach ($books as $book_id => $book) {
        $options[$book_id] = $this->t($book['title']);
    }

    /* Get config */
    $config =  $this->configuration['books_displayed'];

    /* Check if default config is set or null (existing module), if so, display all books; else display selected books. */
    $defaults = ($config === 'all books' || $config === null) ? array_keys($options) : array_keys($config);

    $form['books_displayed'] = [
        '#type' => 'details',
        '#title' => $this->t('Book Selection')
    ];

    $form['books_displayed']['selection'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Books to be displayed in this block'),
      '#options' => $options,
      '#default_value' => $defaults,
      '#description' => $this->t("By default, all books will be added to this menu; otherwise, only the books selected will be displayed."),
    ];

    $form['books_displayed']['select_all_books'] = [
        '#type' => 'button',
        '#value' => $this->t('Select All'),
    ];

    $form['books_displayed']['deselect_all_books'] = [
        '#type' => 'button',
        '#value' => $this->t('Deselect All'),
        '#attributes' => [
            'onclick' => 'return false;'
        ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $config = [];

    $books_to_display = $form_state->getValue('books_displayed');

    if(isset($books_to_display['selection'])){
        foreach($books_to_display['selection'] as $key => $value){
            if($value !== 0){
                $config[$key] = 1;
            }
        }
    }

    /* Set passed form value block page display mode */
    $this->configuration['block_mode'] = $form_state->getValue('book_block_mode');

    /* Set book selection to { } or list of book ids */
    $this->configuration['books_displayed'] = $config;
  }

  public function build() {
    $current_bid = 0;

    if ($node = $this->requestStack->getCurrentRequest()->get('node')) {
      $current_bid = empty($node->book['bid']) ? 0 : $node->book['bid'];
    }

    if ($this->configuration['block_mode'] == 'all pages') {
      $books = $this->bookManager->getAllBooks();

      $books_to_display = $this->configuration['books_displayed'];
     
      if(!$books_to_display){
          return;
      } else {
        foreach($books as $bid => $book){
            if(array_search($bid, array_keys($books_to_display)) === false){
                unset($books[$bid]);
            }
        }
        
        uasort($books, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));

        foreach ($books as $book_id => $book) {
            $this->buildBookTree($book);
        }
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
                    ->execute();

        if($access){
            $tree = $this->bookManager->bookTreeAllData($book['nid']);

            $this->markup .= '<ul id="book-'.$book['nid'].'" class="nav">';
            $this->bookTreeOutput($tree);

            if($this->depth > 1){
              $this->markup .= str_repeat("</ul></li>", $this->depth - 1);
            }

            $this->markup .= '</ul>';

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
                $this->markup .= str_repeat("</ul>", $this->depth - $current_depth);
            }

            $this->markup .= "<li class='nav-item' data-id='".$nid."'>";

            /* If this node has children then we need to print the icon to expand/collapse list. */
            if($has_children){
                $this->markup .= "<a data-toggle='collapse' role='button' aria-expanded='false' aria-controls='nav-trail-".$nid."' href='#nav-trail-".$nid."' class='toggle-icon'><i class='icon fa fa-fw fa-caret-right' aria-hidden='true'></i></a>";
            } else {
                $this->markup .= "<i class='far fa-fw fa-circle icon' aria-hidden='true' data-fa-transform='shrink-9'></i>";
            }

            $this->markup .= "<a href='".$href."' class='nav-link d-inline'>".$title."</a>";

            /* If this node has children we need to also put a list inside the current list element. */
            if ($has_children) {
                $this->markup .= "<ul id='nav-trail-".$nid."' class='nav-list collapse' >";
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