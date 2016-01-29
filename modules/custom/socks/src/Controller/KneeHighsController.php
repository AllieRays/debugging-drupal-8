<?php

/**
 * @file
 * Contains \Drupal\socks\Controller\KneeHighsController.
 */

namespace Drupal\socks\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class KneeHighsController.
 *
 * @package Drupal\socks\Controller
 */
class KneeHighsController extends ControllerBase {
  /**
   * Content.
   *
   * @return string
   *   Return Hello string.
   */
  public function content() {
    $sockContent = \Drupal::service('socks.sock_content');
    $somethings = $sockContent->displaySomethings();

    return [
      '#type' => 'markup',
      '#markup' => t($somethings . 'Just want more Knee High Socks in my life.')
    ];
  }

}
