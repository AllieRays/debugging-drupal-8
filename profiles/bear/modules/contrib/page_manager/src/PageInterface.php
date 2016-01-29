<?php

/**
 * @file
 * Contains \Drupal\page_manager\PageInterface.
 */

namespace Drupal\page_manager;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for page entities.
 */
interface PageInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Returns whether the page entity is enabled.
   *
   * @return bool
   *   Whether the page entity is enabled or not.
   */
  public function status();

  /**
   * Returns the executable instance for this page.
   *
   * @return \Drupal\page_manager\PageExecutableInterface
   */
  public function getExecutable();

  /**
   * Returns the path for the page entity.
   *
   * @return string
   *   The path for the page entity.
   */
  public function getPath();

  /**
   * Indicates if this page is an admin page or not.
   *
   * @return bool
   *   TRUE if this is an admin page, FALSE otherwise.
   */
  public function usesAdminTheme();

  /**
   * Adds a variant to this page.
   *
   * @param \Drupal\page_manager\PageVariantInterface $variant
   *   A page variant entity.
   *
   * @return $this
   */
  public function addVariant(PageVariantInterface $variant);

  /**
   * Retrieves a specific variant.
   *
   * @param string $variant_id
   *   The variant ID.
   *
   * @return \Drupal\page_manager\PageVariantInterface
   *   The variant object.
   */
  public function getVariant($variant_id);

  /**
   * Removes a specific variant.
   *
   * @param string $variant_id
   *   The variant ID.
   *
   * @return $this
   */
  public function removeVariant($variant_id);

  /**
   * Returns the variants available for the entity.
   *
   * @return \Drupal\page_manager\PageVariantInterface[]
   *   An array of the variants.
   */
  public function getVariants();

  /**
   * Returns the conditions used for determining access for this page entity.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getAccessConditions();

  /**
   * Adds a new access condition to the page entity.
   *
   * @param array $configuration
   *   An array of configuration for the new access condition.
   *
   * @return string
   *   The access condition ID.
   */
  public function addAccessCondition(array $configuration);

  /**
   * Retrieves a specific access condition.
   *
   * @param string $condition_id
   *   The access condition ID.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The access condition object.
   */
  public function getAccessCondition($condition_id);

  /**
   * Removes a specific access condition.
   *
   * @param string $condition_id
   *   The access condition ID.
   *
   * @return $this
   */
  public function removeAccessCondition($condition_id);

  /**
   * Returns the logic used to compute access, either 'and' or 'or'.
   *
   * @return string
   *   The string 'and', or the string 'or'.
   */
  public function getAccessLogic();

  /**
   * Returns the static context configurations for this page entity.
   *
   * @return array[]
   *   An array of static context configurations.
   */
  public function getStaticContexts();

  /**
   * Retrieves a specific static context.
   *
   * @param string $name
   *   The static context unique name.
   *
   * @return array
   *   The configuration array of the static context
   */
  public function getStaticContext($name);

  /**
   * Adds/updates a given static context.
   *
   * @param string $name
   *   The static context unique machine name.
   * @param array $configuration
   *   A new array of configuration for the static context.
   *
   * @return $this
   */
  public function setStaticContext($name, $configuration);

  /**
   * Removes a specific static context.
   *
   * @param string $name
   *   The static context unique name.
   *
   * @return $this
   */
  public function removeStaticContext($name);

  /**
   * Gets the values for all defined contexts.
   *
   * @return \Drupal\Component\Plugin\Context\ContextInterface[]
   *   An array of set context values, keyed by context name.
   */
  public function getContexts();

}
