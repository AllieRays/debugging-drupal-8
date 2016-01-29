<?php

/**
 * @file
 * Contains \Drupal\panels\Plugin\DisplayBuilder\DisplayBuilderBase.
 */

namespace Drupal\panels\Plugin\DisplayBuilder;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Provides base class for Display Builder plugins.
 */
abstract class DisplayBuilderBase extends PluginBase implements DisplayBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function build(array $regions, array $context, LayoutInterface $layout = NULL) {
    return $regions;
  }

}
