<?php

/**
 * @file
 * Contains \Drupal\ctools\Plugin\ConditionVariantInterface.
 */

namespace Drupal\ctools\Plugin;

use Drupal\Core\Display\VariantInterface;

/**
 * Provides an interface for variant plugins that use condition plugins.
 */
interface ConditionVariantInterface extends VariantInterface {

  /**
   * Gets the values for all defined contexts.
   *
   * @return \Drupal\Component\Plugin\Context\ContextInterface[]
   *   An array of set contexts, keyed by context name.
   */
  public function getContexts();

  /**
   * Returns the conditions used for determining if this variant is selected.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getSelectionConditions();

  /**
   * Removes a specific selection condition.
   *
   * @param string $condition_id
   *   The selection condition ID.
   *
   * @return $this
   */
  public function removeSelectionCondition($condition_id);

  /**
   * Adds a new selection condition.
   *
   * @param array $configuration
   *   An array of configuration for the new selection condition.
   *
   * @return string
   *   The selection condition ID.
   */
  public function addSelectionCondition(array $configuration);

  /**
   * Returns the logic used to compute selections, either 'and' or 'or'.
   *
   * @return string
   *   The string 'and', or the string 'or'.
   */
  public function getSelectionLogic();

  /**
   * Returns the definition of the plugin implementation.
   *
   * @return array
   *   The plugin definition, as returned by the discovery object used by the
   *   plugin manager.
   */
  public function getPluginDefinition();

  /**
   * Sets the context values for this display variant.
   *
   * @param \Drupal\Component\Plugin\Context\ContextInterface[] $contexts
   *   An array of contexts, keyed by context name.
   *
   * @return $this
   */
  public function setContexts(array $contexts);

  /**
   * Retrieves a specific selection condition.
   *
   * @param string $condition_id
   *   The selection condition ID.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The selection condition object.
   */
  public function getSelectionCondition($condition_id);

}
