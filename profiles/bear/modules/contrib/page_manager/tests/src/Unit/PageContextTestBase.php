<?php

/**
 * @file
 * Contains \Drupal\Tests\page_manager\Unit\PageContextTestBase.
 */

namespace Drupal\Tests\page_manager\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\page_manager\Event\PageManagerContextEvent;
use Drupal\page_manager\PageExecutable;
use Drupal\Tests\UnitTestCase;

/**
 * @todo.
 */
abstract class PageContextTestBase extends UnitTestCase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedDataManager;

  /**
   * The executable for the page entity.
   *
   * @var \Drupal\page_manager\PageExecutable|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $executable;

  /**
   * The event.
   *
   * @var \Drupal\page_manager\Event\PageManagerContextEvent
   */
  protected $event;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->prophesize(TypedDataManager::class);

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('typed_data_manager', $this->typedDataManager->reveal());
    \Drupal::setContainer($container);

    $this->executable = $this->getMockBuilder(PageExecutable::class)
      ->disableOriginalConstructor()
      ->setMethods(['getPage', 'addContext'])
      ->getMock();

    $this->event = new PageManagerContextEvent($this->executable);
  }

}
