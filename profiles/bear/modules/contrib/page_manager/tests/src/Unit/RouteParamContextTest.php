<?php

/**
 * @file
 * Contains \Drupal\Tests\page_manager\Unit\RouteParamContextTest.
 */

namespace Drupal\Tests\page_manager\Unit;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\page_manager\EventSubscriber\RouteParamContext;
use Drupal\page_manager\PageInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Tests the route param context.
 *
 * @coversDefaultClass \Drupal\page_manager\EventSubscriber\RouteParamContext
 *
 * @group PageManager
 */
class RouteParamContextTest extends PageContextTestBase {

  /**
   * @covers ::onPageContext
   */
  public function testOnPageContext() {
    $collection = new RouteCollection();
    $route_provider = $this->prophesize(RouteProviderInterface::class);
    $route_provider->getRoutesByPattern('/test_route')->willReturn($collection);

    $request = new Request();
    $request_stack = new RequestStack();
    $request_stack->push($request);

    $data_definition = new DataDefinition(['type' => 'entity:user']);

    $typed_data = $this->prophesize(TypedDataInterface::class);
    $this->typedDataManager->getDefaultConstraints($data_definition)
      ->willReturn([]);

    $this->typedDataManager->create($data_definition, 'banana')
      ->willReturn($typed_data->reveal());

    $this->typedDataManager->createDataDefinition('bar')
      ->will(function () use ($data_definition) {
        return $data_definition;
      });

    $page = $this->prophesize(PageInterface::class);
    $this->executable->expects($this->once())
      ->method('getPage')
      ->will($this->returnValue($page->reveal()));
    $page->getPath()->willReturn('/test_route');

    $this->executable->expects($this->at(1))
      ->method('addContext')
      ->with('foo', $this->isInstanceOf(Context::class));
    $this->executable->expects($this->at(2))
      ->method('addContext')
      ->with('baz', $this->isInstanceOf(Context::class));

    $collection->add('test_route', new Route('/test_route', [], [], [
      'parameters' => [
        'foo' => ['type' => 'bar'],
        'baz' => ['type' => 'bop'],
        'page' => ['type' => 'entity:page']
      ],
    ]));

    // Set up a request with one of the expected parameters as an attribute.
    $request->attributes->add(['foo' => 'banana']);

    $route_param_context = new RouteParamContext($route_provider->reveal(), $request_stack);
    $route_param_context->onPageContext($this->event);
  }

  /**
   * @covers ::onPageContext
   */
  public function testOnPageContextEmpty() {
    $collection = new RouteCollection();
    $route_provider = $this->prophesize(RouteProviderInterface::class);
    $route_provider->getRoutesByPattern('/test_route')->willReturn($collection);

    $request = new Request();
    $request_stack = new RequestStack();
    $request_stack->push($request);

    $page = $this->prophesize(PageInterface::class);
    $this->executable->expects($this->once())
      ->method('getPage')
      ->will($this->returnValue($page->reveal()));
    $page->getPath()->willReturn('/test_route');

    $this->executable->expects($this->never())
      ->method('addContext');

    // Set up a request with one of the expected parameters as an attribute.
    $request->attributes->add(['foo' => 'banana']);

    $route_param_context = new RouteParamContext($route_provider->reveal(), $request_stack);
    $route_param_context->onPageContext($this->event);
  }

}
