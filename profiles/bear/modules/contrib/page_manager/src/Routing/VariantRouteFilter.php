<?php

/**
 * @file
 * Contains \Drupal\page_manager\Routing\VariantRouteFilter.
 */

namespace Drupal\page_manager\Routing;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\RouteFilterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Filters variant routes.
 *
 * Each variant for a single page has a unique route for the same path, and
 * needs to be filtered. Here is where we run variant selection, which requires
 * gathering contexts.
 */
class VariantRouteFilter implements RouteFilterInterface {

  use RouteEnhancerCollectorTrait;

  /**
   * The page variant storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageVariantStorage;

  /**
   * The current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a new VariantRouteFilter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CurrentPathStack $current_path) {
    $this->pageVariantStorage = $entity_type_manager->getStorage('page_variant');
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    $parameters = $route->getOption('parameters');
    return !empty($parameters['page_manager_page_variant']);
  }

  /**
   * {@inheritdoc}
   *
   * Invalid page manager routes will be removed. Routes not controlled by page
   * manager will be moved to the end of the collection. Once a valid page
   * manager route has been found, all other page manager routes will also be
   * removed.
   */
  public function filter(RouteCollection $collection, Request $request) {
    // Only proceed if the collection is non-empty.
    if (!$collection->count()) {
      return $collection;
    }

    // Store the unaltered request attributes.
    $original_attributes = $request->attributes->all();

    $page_manager_route_found = FALSE;
    foreach ($collection as $name => $route) {
      $attributes = $this->getRequestAttributes($route, $name, $request);

      // Add the enhanced attributes to the request.
      $request->attributes->add($attributes);

      if ($page_variant_id = $route->getDefault('page_manager_page_variant')) {
        // If a page manager route was already found, remove this one from the
        // collection.
        if ($page_manager_route_found) {
          $collection->remove($name);
        }
        elseif ($this->checkPageVariantAccess($page_variant_id)) {
          // Mark that a valid page manager route was found.
          $page_manager_route_found = TRUE;
          // Replace the original attributes with the newly processed attributes.
          $original_attributes = $request->attributes->all();
        }
        else {
          // Remove routes for variants that fail access.
          $collection->remove($name);
        }
      }
      else {
        // If this route has no page variant, move it to the end of the list.
        $collection->add($name, $route);
      }

      // Restore the original request attributes.
      $request->attributes->replace($original_attributes);
    }

    return $collection;
  }

  /**
   * Checks access of a page variant.
   *
   * @param string $page_variant_id
   *   The page variant ID.
   *
   * @return bool
   *   TRUE if the route is valid, FALSE otherwise.
   */
  protected function checkPageVariantAccess($page_variant_id) {
    /** @var \Drupal\page_manager\PageVariantInterface $page */
    $variant = $this->pageVariantStorage->load($page_variant_id);

    try {
      $access = $variant && $variant->access('view');
    }
    // Since access checks can throw a context exception, consider that as
    // a disallowed variant.
    catch (ContextException $e) {
      $access = FALSE;
    }

    return $access;
  }

  /**
   * Prepares the request attributes for use by the selection process.
   *
   * This is be done because route filters run before request attributes are
   * populated.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route.
   * @param string $name
   *   The route name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   An array of request attributes.
   */
  protected function getRequestAttributes(Route $route, $name, Request $request) {
    // Extract the raw attributes from the current path. This performs the same
    // functionality as \Drupal\Core\Routing\UrlMatcher::finalMatch().
    $path = $this->currentPath->getPath($request);
    $raw_attributes = RouteAttributes::extractRawAttributes($route, $name, $path);
    $attributes = $request->attributes->all();
    $attributes = NestedArray::mergeDeep($attributes, $raw_attributes);

    // Run the route enhancers on the raw attributes. This performs the same
    // functionality as \Symfony\Cmf\Component\Routing\DynamicRouter::match().
    foreach ($this->getRouteEnhancers() as $enhancer) {
      $attributes = $enhancer->enhance($attributes, $request);
    }

    return $attributes;
  }

}
