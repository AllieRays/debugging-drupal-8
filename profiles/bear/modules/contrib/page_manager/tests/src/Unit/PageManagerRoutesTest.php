<?php

/**
 * @file
 * Contains \Drupal\Tests\page_manager\Unit\PageManagerRoutesTest.
 */

namespace Drupal\Tests\page_manager\Unit;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Routing\PageManagerRoutes;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests the page manager route subscriber.
 *
 * @coversDefaultClass \Drupal\page_manager\Routing\PageManagerRoutes
 *
 * @group PageManager
 */
class PageManagerRoutesTest extends UnitTestCase {

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked page storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pageStorage;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The tested page route subscriber.
   *
   * @var \Drupal\page_manager\Routing\PageManagerRoutes
   */
  protected $routeSubscriber;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->pageStorage = $this->prophesize(ConfigEntityStorageInterface::class);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage('page')
      ->willReturn($this->pageStorage);
    $this->cacheTagsInvalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);

    $this->routeSubscriber = new PageManagerRoutes($this->entityTypeManager->reveal(), $this->cacheTagsInvalidator->reveal());
  }

  /**
   * Tests adding routes for enabled and disabled pages.
   *
   * @covers ::alterRoutes
   */
  public function testAlterRoutesWithStatus() {
    // Set up a valid page.
    $page1 = $this->prophesize(PageInterface::class);
    $page1->status()
      ->willReturn(TRUE)
      ->shouldBeCalled();
    $page1->getPath()
      ->willReturn('/page1')
      ->shouldBeCalled();
    $page1->id()->willReturn('page1');
    $page1->getVariants()
      ->willReturn(['variant1' => 'variant1']);
    $page1->label()
      ->willReturn('Page label')
      ->shouldBeCalled();
    $page1->usesAdminTheme()
      ->willReturn(TRUE)
      ->shouldBeCalled();
    $pages['page1'] = $page1->reveal();

    // Set up a disabled page.
    $page2 = $this->prophesize(PageInterface::class);
    $page2->status()
      ->willReturn(FALSE)
      ->shouldBeCalled();
    $page2->getVariants()
      ->willReturn(['variant2' => 'variant2']);
    $page2->id()->willReturn('page1');
    $page2->getPath()->willReturn('/page2');
    $pages['page2'] = $page2->reveal();

    $this->pageStorage->loadMultiple()
      ->willReturn($pages)
      ->shouldBeCalledTimes(1);

    $collection = new RouteCollection();
    $route_event = new RouteBuildEvent($collection);
    $this->routeSubscriber->onAlterRoutes($route_event);

    // Only the valid page should be in the collection.
    $this->assertSame(1, $collection->count());
    $route = $collection->get('page_manager.page_view_page1');
    $expected_defaults = [
      '_entity_view' => 'page_manager_page_variant',
      '_title' => 'Page label',
      'page_manager_page_variant' => 'variant1',
      'page_manager_page' => 'page1',
      'base_route_name' => 'page_manager.page_view_page1',
    ];
    $expected_requirements = [
      '_entity_access' => 'page_manager_page.view',
    ];
    $expected_options = [
      'compiler_class' => 'Symfony\Component\Routing\RouteCompiler',
      'parameters' => [
        'page_manager_page_variant' => [
          'type' => 'entity:page_variant',
        ],
        'page_manager_page' => [
          'type' => 'entity:page',
        ],
      ],
      '_admin_route' => TRUE,
    ];
    $this->assertMatchingRoute($route, '/page1', $expected_defaults, $expected_requirements, $expected_options);
  }

  /**
   * Tests overriding an existing route.
   *
   * @covers ::alterRoutes
   * @covers ::findPageRouteName
   *
   * @dataProvider providerTestAlterRoutesOverrideExisting
   */
  public function testAlterRoutesOverrideExisting($page_path, $existing_route_path, $requirements = []) {
    $route_name = 'test_route';
    // Set up a page with the same path as an existing route.
    $page = $this->prophesize(PageInterface::class);
    $page->status()
      ->willReturn(TRUE)
      ->shouldBeCalled();
    $page->getPath()
      ->willReturn($page_path)
      ->shouldBeCalled();
    $page->getVariants()
      ->willReturn(['variant1' => 'variant1']);
    $page->id()->willReturn('page1');
    $page->label()->willReturn(NULL);
    $page->usesAdminTheme()->willReturn(FALSE);

    $this->pageStorage->loadMultiple()
      ->willReturn(['page1' => $page->reveal()])
      ->shouldBeCalledTimes(1);

    $this->cacheTagsInvalidator->invalidateTags(["page_manager_route_name:$route_name"])->shouldBeCalledTimes(1);

    $collection = new RouteCollection();
    $collection->add($route_name, new Route($existing_route_path, ['default_exists' => 'default_value'], $requirements, ['parameters' => ['foo' => 'bar']]));
    $route_event = new RouteBuildEvent($collection);
    $this->routeSubscriber->onAlterRoutes($route_event);

    // The normal route name is not used, the existing route name is instead.
    $this->assertSame(1, $collection->count());
    $this->assertNull($collection->get('page_manager.page_view_page1'));
    $this->assertNull($collection->get('page_manager.page_view_page1_variant1'));

    $route = $collection->get($route_name);
    $expected_defaults = [
      '_entity_view' => 'page_manager_page_variant',
      '_title' => NULL,
      'page_manager_page_variant' => 'variant1',
      'page_manager_page' => 'page1',
      'base_route_name' => $route_name,
    ];
    $expected_requirements = $requirements;
    $expected_options = [
      'compiler_class' => 'Symfony\Component\Routing\RouteCompiler',
      'parameters' => [
        'page_manager_page_variant' => [
          'type' => 'entity:page_variant',
        ],
        'page_manager_page' => [
          'type' => 'entity:page',
        ],
        'foo' => 'bar',
      ],
      '_admin_route' => FALSE,
    ];
    $this->assertMatchingRoute($route, $existing_route_path, $expected_defaults, $expected_requirements, $expected_options);
  }

  public function providerTestAlterRoutesOverrideExisting() {
    $data = [];
    $data['no_slug'] = ['/test_route', '/test_route'];
    $data['slug'] = ['/test_route/{test_route}', '/test_route/{test_route}'];
    $data['placeholder'] = ['/test_route/%', '/test_route/{test_route}'];
    $data['slug_with_default'] = ['/test_route/{default_exists}', '/test_route/{default_exists}'];
    $data['placeholder_with_default'] = ['/test_route/%', '/test_route/{default_exists}'];
    $data['with_requirement'] = ['/test_route/{foo}', '/test_route/{foo}', ['foo' => '\d+']];
    return $data;
  }

  /**
   * Asserts that a route object has the expected properties.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to test.
   * @param string $expected_path
   *   The expected path for the route.
   * @param array $expected_defaults
   *   The expected defaults for the route.
   * @param array $expected_requirements
   *   The expected requirements for the route.
   * @param array $expected_options
   *   The expected options for the route.
   */
  protected function assertMatchingRoute(Route $route, $expected_path, $expected_defaults, $expected_requirements, $expected_options) {
    $this->assertSame($expected_path, $route->getPath());
    $this->assertSame($expected_defaults, $route->getDefaults());
    $this->assertSame($expected_requirements, $route->getRequirements());
    $this->assertSame($expected_options, $route->getOptions());
  }

}
