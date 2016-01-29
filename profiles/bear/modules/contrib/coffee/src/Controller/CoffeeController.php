<?php

/**
 * @file
 * Contains \Drupal\coffee\Controller\CoffeeController.
 */

namespace Drupal\coffee\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;


/**
 * Provides route responses for coffee.module.
 */
class CoffeeController extends ControllerBase {

  /**
   * Outputs the data that is used for the Coffee autocompletion in JSON.
   */
  public function coffeeData() {
    $output = array();

    // Get configured menus from configuration.
    $menus = \Drupal::config('coffee.configuration')->get('coffee_menus');
    if ($menus !== NULL) {
      foreach ($menus as $v) {
        if ($v === '0') {
          continue;
        }

        // Build the menu tree.
        $menu_tree_parameters = new MenuTreeParameters();
        $tree = \Drupal::menuTree()->load($v, $menu_tree_parameters);

        foreach ($tree as $key => $link) {

          $command = ($v == 'user-menu') ? ':user' : NULL;
          $this->coffee_traverse_below($link, $output, $command);

        }
      }
    }

    module_load_include('inc', 'coffee', 'coffee.hooks');
    $commands = array();

    foreach (\Drupal::moduleHandler()
                    ->getImplementations('coffee_commands') as $module) {
      $commands = array_merge($commands, \Drupal::moduleHandler()
                                                ->invoke($module, 'coffee_commands', array()));
    }

    if (!empty($commands)) {
      $output = array_merge($output, $commands);
    }

    foreach ($output as $k => $v) {
      if ($v['value'] == '<front>') {
        unset($output[$k]);
        continue;
      }

      // Filter out XSS.
      $output[$k]['label'] = Xss::filter($output[$k]['label']);

    }

    // Re-index the array.
    $output = array_values($output);

    return new JsonResponse($output);
  }

  /**
   * Function coffee_traverse_below().
   *
   * Helper function to traverse down through a menu structure.
   */
  protected function coffee_traverse_below($link, &$output, $command = NULL) {
    $l = isset($link->link) ? $link->link : array();

    // Only add link if user has access.

    $url = $l->getUrlObject();
    if ($url->access()) {
      $title = $l->getTitle();
      $label = (!empty($title) ? $title : 'test');
      $output[] = array(
        'value' => $url->toString(),
        'label' => $label,
        'command' => $command,
      );
    }


    if ($link->subtree) {
      foreach ($link->subtree as $below_link) {
        $this->coffee_traverse_below($below_link, $output);
      }
    }

    $manager = \Drupal::service('plugin.manager.menu.local_task');

    $local_tasks = $manager->getLocalTasksForRoute($l->getRouteName());
    if ($local_tasks) {
      $command = NULL;
      foreach ($local_tasks as $key => $local_task_link) {
        $this->coffee_traverse_local_tasks($local_task_link, $output);
      }
    }


  }

  /**
   * Helper function to traverse the local tasks.
   */
  protected function coffee_traverse_local_tasks($local_task_link, &$output) {
    if (is_array($local_task_link)) {
      foreach ($local_task_link as $key => $local_task) {
        $this->coffee_traverse_local_tasks($local_task, $output);
      }
    }
    else {
      $local_task = $local_task_link;
    }

    if (is_object($local_task)) {

      $route_name = $local_task->getPluginDefinition()['route_name'];
      $route_parameters = $local_task->getPluginDefinition()['route_parameters'];
      $url = Url::fromRoute($route_name, $route_parameters);

      $label = $local_task->getTitle();
      if ($url->access() && !$url->isRouted()) {
        $output[] = array(
          'value' => $url,
          'label' => $label,
          'command' => 'NULL',
        );
      }
    }

  }
}
