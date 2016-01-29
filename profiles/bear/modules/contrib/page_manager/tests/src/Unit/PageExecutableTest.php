<?php

/**
 * @file
 * Contains \Drupal\Tests\page_manager\Unit\PageExecutableTest.
 */

namespace Drupal\Tests\page_manager\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\page_manager\Event\PageManagerContextEvent;
use Drupal\page_manager\Event\PageManagerEvents;
use Drupal\page_manager\PageExecutable;
use Drupal\page_manager\PageInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests the PageExecutable.
 *
 * @coversDefaultClass \Drupal\page_manager\PageExecutable
 *
 * @group PageManager
 */
class PageExecutableTest extends UnitTestCase {

  /**
   * @var \Drupal\page_manager\PageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $page;

  /**
   * @var \Drupal\page_manager\PageExecutable
   */
  protected $exectuable;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    parent::setUp();
    $this->page = $this->prophesize(PageInterface::class);
    $this->exectuable = new PageExecutable($this->page->reveal());
  }

  /**
   * @covers ::getPage
   */
  public function testGetPage() {
    $this->assertSame($this->page->reveal(), $this->exectuable->getPage());
  }

  /**
   * @covers ::addContext
   */
  public function testAddContext() {
    $context = new Context(new ContextDefinition('bar'));
    $this->exectuable->addContext('foo', $context);
    $contexts = $this->exectuable->getContexts();
    $this->assertSame(['foo' => $context], $contexts);
  }

  /**
   * @covers ::getContexts
   */
  public function testGetContexts() {
    $context = new Context(new ContextDefinition('bar'));

    $event_dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $event_dispatcher->dispatch(PageManagerEvents::PAGE_CONTEXT, Argument::type(PageManagerContextEvent::class))
      ->will(function ($args) use ($context) {
        $args[1]->getPageExecutable()->addContext('foo', $context);
      });

    $container = new ContainerBuilder();
    $container->set('event_dispatcher', $event_dispatcher->reveal());
    \Drupal::setContainer($container);

    $contexts = $this->exectuable->getContexts();
    $this->assertSame(['foo' => $context], $contexts);
  }

}
