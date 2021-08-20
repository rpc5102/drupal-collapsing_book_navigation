<?php

namespace Drupal\collapsing_book_navigation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\book\BookManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Provides a 'Book navigation' block.
 *
 * @Block(
 *   id = "custom_book_navigation",
 *   admin_label = @Translation("Book navigation - Collapsible"),
 *   category = @Translation("Menus")
 * )
 */
class CustomBookNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The book manager.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected $bookManager;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs a new BookNavigationBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   The book manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   The node storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, BookManagerInterface $book_manager, EntityStorageInterface $node_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->bookManager = $book_manager;
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('book.manager'),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

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

    unset($options);

    /* Build a list of books by weights */
    $books = $this->bookManager->getAllBooks();

    uasort($books, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($books as $book_id => $book) {
      $options[$book_id] = $this->t('@title', ['@title' => $book['title']]);
    }

    /* Get config */
    $config =  $this->configuration['books_displayed'];

    /* Check if default config is set or null (existing module), if so, display all books; else display selected books. */
    $defaults = ($config === 'all books' || $config === NULL) ? array_keys($options) : array_keys($config);

    $form['books_displayed'] = [
      '#type' => 'details',
      '#title' => $this->t('Book Selection'),
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
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $config = [];

    $books_to_display = $form_state->getValue('books_displayed');

    if (isset($books_to_display['selection'])) {
      foreach ($books_to_display['selection'] as $key => $value) {
        if ($value !== 0) {
          $config[$key] = 1;
        }
      }
    }

    /* Set passed form value block page display mode */
    $this->configuration['block_mode'] = $form_state->getValue('book_block_mode');

    /* Set book selection to { } or list of book ids */
    $this->configuration['books_displayed'] = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_bid = 0;

    $node = $this->routeMatch->getParameter('node');
    
    if ($node instanceof NodeInterface && !empty($node->book['bid'])) {
      $current_bid = $node->book['bid'];
    }
    if ($this->configuration['block_mode'] == 'all pages') {
      $book_menus = [];

      $books = $this->bookManager->getAllBooks();
      uasort($books, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      $books_to_display = $this->configuration['books_displayed'];

      if ($books_to_display === 'all books') {
        $books_to_display = $books;
      }

      if (!$books_to_display) {
        return;
      }
      
      foreach ($books as $book_id => $book) {
        if (array_search($book_id, array_keys($books_to_display)) !== FALSE) {
          $book_node = $this->nodeStorage->load($book['nid']);
          $data = $this->bookManager->bookTreeAllData($book_node->book['bid'], $book_node->book); 

          $data[key($data)]['link']['access'] = $book_node->access('view');

          $book_menus[$book_id] = $this->bookManager->bookTreeOutput($data);

          $book_menus[$book_id] += [
            '#book_title' => $book['title'],
          ];
        }
      }
      if ($book_menus) {
        //kint($book_menus);
        return [
          '#theme' => 'book_all_books_block',
        ] + $book_menus;
      }
    }
    elseif ($current_bid) {
      // Only display this block when the user is browsing a book and do
      // not show unpublished books.
      $nid = \Drupal::entityQuery('node')
        ->accessCheck(TRUE)
        ->condition('nid', $node->book['bid'], '=')
        ->condition('status', NodeInterface::PUBLISHED)
        ->execute();

      // Only show the block if the user has view access for the top-level node.
      if ($nid) {
        $tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);
        // There should only be one element at the top level.
        $data = array_shift($tree);
        $below = $this->bookManager->bookTreeOutput($data['below']);
        if (!empty($below)) {
          return $below;
        }
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.book_navigation']);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
