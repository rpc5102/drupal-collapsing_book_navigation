<?php

namespace Drupal\collapsing_book_navigation;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
  
/** 
 * Defines a book manager which extends the core BookManager class.
 */ 
class CollapsingBookNavigationServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {
  
  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('book.manager');
    $definition->setClass('Drupal\collapsing_book_navigation\CollapsingBookManager');
  }

  
}
