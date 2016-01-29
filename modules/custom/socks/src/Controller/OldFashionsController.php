<?php

/**
 * @file
 * Contains \Drupal\socks\Controller\OldFashionsController.
 */

namespace Drupal\socks\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class OldFashionsController.
 *
 * @package Drupal\socks\Controller
 */
class OldFashionsController extends ControllerBase {
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
      '#markup' => t($somethings . 'Although Old Fashion Socks are my favorite.')
    ];
  }

}
