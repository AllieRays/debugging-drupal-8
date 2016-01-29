<?php

/**
 * @file
 * Contains \Drupal\panels\Plugin\DisplayBuilder\DisplayBuilderInterface.
 */

namespace Drupal\panels\Plugin\DisplayBuilder;

use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;

/**
 * Defines the DisplayBuilder plugin type.
 */
interface DisplayBuilderInterface {

  /**
   * Renders a Panels display.
   *
   * This is the outermost method in the Panels render pipeline. It calls the
   * inner methods, which return a content array, which is in turn passed to the
   * theme function specified in the layout plugin.
   *
   * @param array $regions
   *   The render array representing regions.
   * @param array $contexts
   *   The array of context objects.
   * @param \Drupal\layout_plugin\Plugin\Layout\LayoutInterface
   *   (optional) The layout plugin.
   * @return array
   *   Render array modified by the display builder.
   */
  public function build(array $regions, array $contexts, LayoutInterface $layout = NULL);

}
