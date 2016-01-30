<?php

/**
 * @file
 * Contains \Drupal\socks\Controller\SockController.
 */
namespace Drupal\socks\Controller;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class SockController.
 *
 * @package Drupal\socks\Controller
 */
class SockController extends ControllerBase {
  /**
   * Content
   * @return string
   *   Return Hello string.
   *
   */
  public function content() {
    $sockContent = \Drupal::service('socks.sock_content');
    $somethings = $sockContent->displaySomething();

    return [
      '#type' => 'markup',
      '#markup' => t($somethings)
    ];
  }

}
