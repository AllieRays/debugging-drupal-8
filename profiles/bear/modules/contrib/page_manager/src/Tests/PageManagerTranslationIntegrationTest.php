<?php

/**
 * @file
 * Contains \Drupal\page_manager\Tests\PageManagerTranslationIntegrationTest.
 */

namespace Drupal\page_manager\Tests;

use Drupal\content_translation\Tests\ContentTranslationTestBase;

/**
 * Tests that overriding the entity page does not affect content translation.
 *
 * @group page_manager
 */
class PageManagerTranslationIntegrationTest extends ContentTranslationTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'page_manager', 'node', 'content_translation'];

  /**
   * {@inheritdoc}
   */
  protected $entityTypeId = 'node';

  /**
   * {@inheritdoc}
   */
  protected $bundle = 'article';

  /**
   * {@inheritdoc}
   */
  protected function setupBundle() {
    parent::setupBundle();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatorPermissions() {
    return array_merge(parent::getTranslatorPermissions(), ['administer pages', 'administer pages']);
  }

  /**
   * Tests that overriding the node page does not prevent translation.
   */
  public function testNode() {
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');

    $node = $this->drupalCreateNode(['type' => 'article']);
    $this->drupalGet('node/' . $node->id());
    $this->assertResponse(200);
    $this->assertText($node->label());
    $this->clickLink('Translate');
    $this->assertResponse(200);

    // Create a new page entity to take over node pages.
    $edit = [
      'label' => 'Node View',
      'id' => 'node_view',
      'path' => 'node/%',
    ];
    $this->drupalPostForm('admin/structure/page_manager/add', $edit, 'Save');

    // Create a new variant.
    $edit = [
      'id' => 'http_status_code',
      'variant_settings[status_code]' => 200,
    ];
    $this->drupalPostForm('admin/structure/page_manager/manage/node_view/add/http_status_code', $edit, 'Save');

    $this->drupalGet('node/' . $node->id());
    $this->assertResponse(200);
    $this->clickLink('Translate');
    $this->assertResponse(200);
  }

}
