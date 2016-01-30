<?php

/**
 * @file
 * Contains Drupal\socks\Entity\Sock.
 *
 * This contains our entity class.
 *
 * Originally based on code from blog post at
 * http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 */

namespace Drupal\socks\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the sock entity.

 */
class Sock extends ConfigEntityBase {

  /**
   * The sock ID.
   *
   * @var string
   */
  public $id;
  /**
   * The sock UUID.
   *
   * @var string
   */
  public $uuid;
  /**
   * The sock label.
   *
   * @var string
   */
  public $label;
  /**
   * The sock's description.
   *
   * @var string
   */
  public $description;
  /**
   * The sock's fabric.
   *
   * @var string
   */
  public $fabric;
  /**
   * The sock's rating.
   *
   * @var string
   */
  public $rating;

}
