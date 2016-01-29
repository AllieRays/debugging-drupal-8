<?php

/**
 * @file
 * Contains \Drupal\ctools\Plugin\DisplayVariant\BlockDisplayVariant.
 */

namespace Drupal\ctools\Plugin\DisplayVariant;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Display\VariantBase;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\ctools\Plugin\BlockVariantInterface;
use Drupal\ctools\Plugin\BlockVariantTrait;
use Drupal\ctools\Plugin\ConditionVariantInterface;
use Drupal\ctools\Plugin\ConditionVariantTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for a display variant that simply contains blocks.
 */
abstract class BlockDisplayVariant extends VariantBase implements ContextAwareVariantInterface, ConditionVariantInterface, ContainerFactoryPluginInterface, BlockVariantInterface, RefinableCacheableDependencyInterface {

  use AjaxFormTrait;
  use BlockVariantTrait;
  use ConditionVariantTrait;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * An array of collected contexts.
   *
   * This is only used on runtime, and is not stored.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $contexts = [];

  /**
   * Constructs a new BlockDisplayVariant.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   The UUID generator.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContextHandlerInterface $context_handler, AccountInterface $account, UuidInterface $uuid_generator, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contextHandler = $context_handler;
    $this->account = $account;
    $this->uuidGenerator = $uuid_generator;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('context.handler'),
      $container->get('current_user'),
      $container->get('uuid'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    // Delegate to the conditions.
    return $this->determineSelectionAccess($this->getContexts());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'blocks' => [],
      'selection_conditions' => [],
      'selection_logic' => 'and',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    foreach ($this->getBlockCollection() as $instance) {
      $this->calculatePluginDependencies($instance);
    }
    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }
    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'selection_conditions' => $this->getSelectionConditions()->getConfiguration(),
      'blocks' => $this->getBlockCollection()->getConfiguration(),
    ] + parent::getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->configuration['selection_logic'];
  }

  /**
   * Gets the contexts.
   *
   * @return \Drupal\Component\Plugin\Context\ContextInterface[]
   *   An array of set contexts, keyed by context name.
   */
  public function getContexts() {
    return $this->contexts;
  }

  /**
   * Sets the contexts.
   *
   * @param \Drupal\Component\Plugin\Context\ContextInterface[] $contexts
   *   An array of contexts, keyed by context name.
   *
   * @return $this
   */
  public function setContexts(array $contexts) {
    $this->contexts = $contexts;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function contextHandler() {
    return $this->contextHandler;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectionConfiguration() {
    return $this->configuration['selection_conditions'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getBlockConfig() {
    return $this->configuration['blocks'];
  }

  /**
   * {@inheritdoc}
   */
  protected function uuidGenerator() {
    return $this->uuidGenerator;
  }

}
