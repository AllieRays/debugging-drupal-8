<?php

/**
 * @file
 * Contains Drupal\page_manager\Entity\PageVariant.
 */

namespace Drupal\page_manager\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\ctools\Plugin\ConditionVariantTrait;
use Drupal\page_manager\PageVariantInterface;

/**
 * Defines the page variant entity.
 *
 * @ConfigEntityType(
 *   id = "page_variant",
 *   label = @Translation("Variant"),
 *   handlers = {
 *     "access" = "Drupal\page_manager\Entity\PageVariantAccess",
 *     "view_builder" = "Drupal\page_manager\Entity\PageVariantViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\page_manager\Form\PageVariantAddForm",
 *       "edit" = "Drupal\page_manager\Form\PageVariantEditForm",
 *       "delete" = "Drupal\page_manager\Form\PageVariantDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer pages",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "variant",
 *     "variant_settings",
 *     "page",
 *     "weight",
 *     "selection_criteria",
 *     "selection_logic",
 *     "contexts"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/page_manager/manage/{page}/variant/{page_variant}",
 *     "delete-form" = "/admin/structure/page_manager/manage/{page}/variant/{page_variant}/delete",
 *   },
 *   lookup_keys = {
 *     "page"
 *   }
 * )
 */
class PageVariant extends ConfigEntityBase implements PageVariantInterface {

  use ConditionVariantTrait;

  /**
   * The ID of the page variant entity.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the page variant entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The weight of the page variant entity.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * The UUID of the page variant entity.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The ID of the variant plugin.
   *
   * @var string
   */
  protected $variant;

  /**
   * The plugin configuration for the variant plugin.
   *
   * @var array
   */
  protected $variant_settings = [];

  /**
   * The ID of the page entity this page variant entity belongs to.
   *
   * @var string
   */
  protected $page;

  /**
   * The plugin configuration for the selection criteria condition plugins.
   *
   * @var array
   */
  protected $selection_criteria = [];

  /**
   * The selection logic for this page variant entity (either 'and' or 'or').
   *
   * @var string
   */
  protected $selection_logic = 'and';

  /**
   * An array of collected contexts.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $contexts = [];

  /**
   * The plugin collection that holds the single variant plugin instance.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $variantPluginCollection;

  /**
   * {@inheritdoc}
   */
  protected function invalidateTagsOnSave($update) {
    parent::invalidateTagsOnSave($update);

    // The parent doesn't invalidate the entity cache tags on save because the
    // config system will invalidate them, but since we're using the parent
    // page's cache tags, we need to invalidate them special.
    Cache::invalidateTags($this->getCacheTagsToInvalidate());
  }

  /**
   * {@inheritdoc}
   */
  protected static function invalidateTagsOnDelete(EntityTypeInterface $entity_type, array $entities) {
    parent::invalidateTagsOnDelete($entity_type, $entities);

    // The parent doesn't invalidate the entity cache tags on delete because the
    // config system will invalidate them, but since we're using the parent
    // page's cache tags, we need to invalidate them special.
    $tags = [];
    foreach ($entities as $entity) {
      $tags = Cache::mergeTags($tags, $entity->getCacheTagsToInvalidate());
    }
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    // We use the same cache tags as the parent page.
    return $this->getPage()->getCacheTagsToInvalidate();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    $this->addDependency('config', $this->getPage()->getConfigDependencyName());

    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }

    return $this->getDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'selection_criteria' => $this->getSelectionConditions(),
      'variant_settings' => $this->getVariantPluginCollection(),
    ];
  }

  /**
   * Get the plugin collection that holds the single variant plugin instance.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The plugin collection that holds the single variant plugin instance.
   */
  protected function getVariantPluginCollection() {
    if (!$this->variantPluginCollection) {
      $this->variantPluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.display_variant'), $this->variant, $this->variant_settings);
    }
    return $this->variantPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantPlugin() {
    return $this->getVariantPluginCollection()->get($this->variant);
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantPluginId() {
    return $this->variant;
  }

  /**
   * Gets the page this variant is on.
   *
   * @return \Drupal\page_manager\Entity\Page
   */
  protected function getPage() {
    if (!$this->page) {
      throw new \UnexpectedValueException('The page variant has no associated page');
    }
    return Page::load($this->page);
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return array_merge($this->getPage()->getContexts(), $this->contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->get('selection_logic');
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectionConfiguration() {
    return $this->get('selection_criteria');
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    $parameters['page'] = $this->get('page');
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    static::routeBuilder()->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    static::routeBuilder()->setRebuildNeeded();
  }

  /**
   * Wraps the route builder.
   *
   * @return \Drupal\Core\Routing\RouteBuilderInterface
   *   An object for state storage.
   */
  protected static function routeBuilder() {
    return \Drupal::service('router.builder');
  }

  /**
   * Wraps the context handler.
   *
   * @return \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected function contextHandler() {
    return \Drupal::service('context.handler');
  }

}
