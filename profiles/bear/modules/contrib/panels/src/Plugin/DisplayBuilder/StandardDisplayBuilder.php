<?php

/**
 * @file
 * Contains \Drupal\panels\Plugin\DisplayBuilder\StandardDisplayBuilder.
 */

namespace Drupal\panels\Plugin\DisplayBuilder;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The standard display builder for viewing a PanelsDisplayVariant.
 *
 * @DisplayBuilder(
 *   id = "standard",
 *   label = @Translation("Standard")
 * )
 */
class StandardDisplayBuilder extends DisplayBuilderBase implements ContainerFactoryPluginInterface {

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new PanelsDisplayVariant.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContextHandlerInterface $context_handler, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contextHandler = $context_handler;
    $this->account = $account;
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
      $container->get('current_user')
    );
  }

  /**
   * Build render arrays for each of the regions.
   *
   * @param array $regions
   *   The render array representing regions.
   * @param array $contexts
   *   The array of context objects.
   *
   * @return array
   *   An associative array, keyed by region ID, containing the render arrays
   *   representing the content of each region.
   */
  protected function buildRegions(array $regions, array $contexts) {
    $build = [];
    foreach ($regions as $region => $blocks) {
      if (!$blocks) {
        continue;
      }

      $region_name = Html::getClass("block-region-$region");
      $build[$region]['#prefix'] = '<div class="' . $region_name . '">';
      $build[$region]['#suffix'] = '</div>';

      /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
      $weight = 0;
      foreach ($blocks as $block_id => $block) {
        if ($block instanceof ContextAwarePluginInterface) {
          $this->contextHandler->applyContextMapping($block, $contexts);
        }
        if ($block->access($this->account)) {
          $block_render_array = [
            '#theme' => 'block',
            '#attributes' => [],
            '#weight' => $weight++,
            '#configuration' => $block->getConfiguration(),
            '#plugin_id' => $block->getPluginId(),
            '#base_plugin_id' => $block->getBaseId(),
            '#derivative_plugin_id' => $block->getDerivativeId(),
          ];
          $block_render_array['content'] = $block->build();

          $build[$region][$block_id] = $block_render_array;
        }
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions, array $contexts, LayoutInterface $layout = NULL) {
    $regions = $this->buildRegions($regions, $contexts);
    if ($layout) {
      $regions = $layout->build($regions);
    }
    return $regions;
  }

}
