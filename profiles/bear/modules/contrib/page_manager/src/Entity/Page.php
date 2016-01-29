<?php

/**
 * @file
 * Contains \Drupal\page_manager\Entity\Page.
 */

namespace Drupal\page_manager\Entity;

use Drupal\page_manager\PageInterface;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\page_manager\PageVariantInterface;

/**
 * Defines a Page entity class.
 *
 * @ConfigEntityType(
 *   id = "page",
 *   label = @Translation("Page"),
 *   handlers = {
 *     "access" = "Drupal\page_manager\Entity\PageAccess",
 *     "list_builder" = "Drupal\page_manager\Entity\PageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\page_manager\Form\PageAddForm",
 *       "edit" = "Drupal\page_manager\Form\PageEditForm",
 *       "delete" = "Drupal\page_manager\Form\PageDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer pages",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "use_admin_theme",
 *     "path",
 *     "access_logic",
 *     "access_conditions",
 *     "static_context",
 *   },
 *   links = {
 *     "collection" = "/admin/structure/page_manager",
 *     "add-form" = "/admin/structure/page_manager/add",
 *     "edit-form" = "/admin/structure/page_manager/manage/{page}",
 *     "delete-form" = "/admin/structure/page_manager/manage/{page}/delete",
 *     "enable" = "/admin/structure/page_manager/manage/{page}/enable",
 *     "disable" = "/admin/structure/page_manager/manage/{page}/disable"
 *   }
 * )
 */
class Page extends ConfigEntityBase implements PageInterface {

  /**
   * The ID of the page entity.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the page entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The path of the page entity.
   *
   * @var string
   */
  protected $path;

  /**
   * The page variant entities.
   *
   * @var \Drupal\page_manager\PageVariantInterface[].
   */
  protected $variants;

  /**
   * The configuration of access conditions.
   *
   * @var array
   */
  protected $access_conditions = [];

  /**
   * Tracks the logic used to compute access, either 'and' or 'or'.
   *
   * @var string
   */
  protected $access_logic = 'and';

  /**
   * The plugin collection that holds the access conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $accessConditionCollection;

  /**
   * Indicates if this page should be displayed in the admin theme.
   *
   * @var bool
   */
  protected $use_admin_theme;

  /**
   * Static context references.
   *
   * A list of arrays with the keys name, label, type and value.
   *
   * @var array[]
   */
  protected $static_context = [];

  /**
   * Stores a reference to the executable version of this page.
   *
   * This is only used on runtime, and is not stored.
   *
   * @var \Drupal\page_manager\PageExecutable
   */
  protected $executable;

  /**
   * Returns a factory for page executables.
   *
   * @return \Drupal\page_manager\PageExecutableFactoryInterface
   */
  protected function executableFactory() {
    return \Drupal::service('page_manager.executable_factory');
  }

  /**
   * {@inheritdoc}
   */
  public function getExecutable() {
    if (!isset($this->executable)) {
      $this->executable = $this->executableFactory()->get($this);
    }
    return $this->executable;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function usesAdminTheme() {
    return isset($this->use_admin_theme) ? $this->use_admin_theme : strpos($this->getPath(), '/admin/') === 0;
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);
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
   * Wraps the entity storage for page variants.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function variantStorage() {
    return \Drupal::service('entity_type.manager')->getStorage('page_variant');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'access_conditions' => $this->getAccessConditions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessConditions() {
    if (!$this->accessConditionCollection) {
      $this->accessConditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('access_conditions'));
    }
    return $this->accessConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addAccessCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getAccessConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessCondition($condition_id) {
    return $this->getAccessConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeAccessCondition($condition_id) {
    $this->getAccessConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessLogic() {
    return $this->access_logic;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticContexts() {
    return $this->static_context;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticContext($name) {
    if (isset($this->static_context[$name])) {
      return $this->static_context[$name];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setStaticContext($name, $configuration) {
    $this->static_context[$name] = $configuration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeStaticContext($name) {
    unset($this->static_context[$name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return $this->getExecutable()->getContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function addVariant(PageVariantInterface $variant) {
    $this->variants[$variant->id()] = $variant;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariant($variant_id) {
    $variants = $this->getVariants();
    if (!isset($variants[$variant_id])) {
      throw new \UnexpectedValueException('The requested variant does not exist or is not associated with this page');
    }
    return $variants[$variant_id];
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariant($variant_id) {
    $this->getVariant($variant_id)->delete();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariants() {
    if (!isset($this->variants)) {
      $this->variants = [];
      /** @var \Drupal\page_manager\PageVariantInterface $variant */
      foreach ($this->variantStorage()->loadByProperties(['page' => $this->id()]) as $variant) {
        $this->variants[$variant->id()] = $variant;
      }
      // Suppress errors because of https://bugs.php.net/bug.php?id=50688.
      @uasort($this->variants, [$this, 'variantSortHelper']);
    }
    return $this->variants;
  }

  /**
   * {@inheritdoc}
   */
  public function variantSortHelper($a, $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      return 0;
    }

    return ($a_weight < $b_weight) ? -1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = parent::__sleep();

    // Avoid serializing plugin collections and the page executable as they
    // might contain references to a lot of objects including the container.
    $unset_vars = [
      'variants' => NULL,
      'accessConditionCollection' => 'access_variants',
      'executable' => NULL,
    ];
    foreach ($unset_vars as $unset_var => $configuration_key) {
      if (!empty($this->$unset_var)) {
        if ($configuration_key) {
          $this->set($configuration_key, $this->$unset_var->getConfiguration());
        }
        unset($vars[array_search($unset_var, $vars)]);
      }
    }

    return $vars;
  }

}
