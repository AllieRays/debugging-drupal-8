<?php

/**
 * @file
 * Contains \Drupal\socks\Controller\AnkleBitersController.
 */

namespace Drupal\socks\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AnkleBitersController.
 *
 * @package Drupal\socks\Controller
 */

class AnkleBitersController extends ControllerBase {
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
      '#markup' => t($somethings . 'Can never go wrong with Ankle High Socks though. ')
    ];
  }

}
