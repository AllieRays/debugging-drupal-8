<?php

/**
 * @file
 * Contains \Drupal\Tests\page_manager\Unit\PageTest.
 */

namespace Drupal\Tests\page_manager\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\page_manager\Entity\Page;
use Drupal\page_manager\PageVariantInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Page entity.
 *
 * @coversDefaultClass \Drupal\page_manager\Entity\Page
 *
 * @group PageManager
 */
class PageTest extends UnitTestCase {

  /**
   * @covers ::getVariants
   */
  public function testGetVariants() {
    $variant1 = $this->prophesize(PageVariantInterface::class);
    $variant1->id()->willReturn('variant1');
    $variant1->getWeight()->willReturn(0);
    $variant2 = $this->prophesize(PageVariantInterface::class);
    $variant2->id()->willReturn('variant2');
    $variant2->getWeight()->willReturn(-10);

    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage
      ->loadByProperties(['page' => 'the_page'])
      ->willReturn(['variant1' => $variant1->reveal(), 'variant2' => $variant2->reveal()])
      ->shouldBeCalledTimes(1);

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('page_variant')->willReturn($entity_storage);

    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $entity_type_manager->reveal());
    \Drupal::setContainer($container);

    $page = new Page(['id' => 'the_page'], 'page');
    $variants = $page->getVariants();
    $this->assertSame(['variant2' => $variant2->reveal(), 'variant1' => $variant1->reveal()], $variants);
    $variants = $page->getVariants();
    $this->assertSame(['variant2' => $variant2->reveal(), 'variant1' => $variant1->reveal()], $variants);
  }

}
