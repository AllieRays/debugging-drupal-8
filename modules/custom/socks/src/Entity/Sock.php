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
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - handlers: An array of entity handler classes, keyed by handler type.
 *    - access: The class that is used for access checks.
 *    - list_builder: The class that provides listings of the entity.
 *    - form: An array of entity form classes keyed by their operation.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *  - links: entity URL definitions. These are mostly used for Field UI.
 *    Arbitrary keys can set here. For example, User sets cancel-form, while
 *    Node uses delete-form.
 *
 * @see http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 * @see annotation
 * @see Drupal\Core\Annotation\Translation
 *
 * @ingroup socks
 *
 * @ConfigEntityType(
 *   id = "sock",
 *   label = @Translation("Sock"),
 *   admin_permission = "administer socks",
 *   handlers = {
 *     "access" = "Drupal\socks\SockAccessController",
 *     "list_builder" = "Drupal\socks\Controller\SockListBuilder",
 *     "form" = {
 *       "add" = "Drupal\socks\Form\SockAddForm",
 *       "edit" = "Drupal\socks\Form\SockEditForm",
 *       "delete" = "Drupal\socks\Form\SockDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/examples/socks/manage/{sock}",
 *     "delete-form" = "/examples/socks/manage/{sock}/delete"
 *   }
 * )
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
