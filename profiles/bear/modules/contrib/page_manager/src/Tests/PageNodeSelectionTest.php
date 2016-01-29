<?php

/**
 * @file
 * Contains \Drupal\page_manager\Tests\PageNodeSelectionTest.
 */

namespace Drupal\page_manager\Tests;

use Drupal\page_manager\Entity\Page;
use Drupal\simpletest\WebTestBase;

/**
 * Tests selecting variants based on nodes.
 *
 * @group page_manager
 */
class PageNodeSelectionTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['page_manager', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Page']);
    $this->drupalLogin($this->drupalCreateUser(['administer pages', 'create article content', 'create page content']));

    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests that a node bundle condition controls the node view page.
   */
  public function testAdmin() {
    // Create two nodes, and view their pages.
    $node1 = $this->drupalCreateNode(['type' => 'page']);
    $node2 = $this->drupalCreateNode(['type' => 'article']);
    $node3 = $this->drupalCreateNode(['type' => 'article']);
    $this->drupalGet('node/' . $node1->id());
    $this->assertResponse(200);
    $this->assertText($node1->label());
    $this->assertTitle($node1->label() . ' | Drupal');
    $this->drupalGet('node/' . $node2->id());
    $this->assertResponse(200);
    $this->assertCacheTag('page_manager_route_name:entity.node.canonical');
    $this->assertText($node2->label());
    $this->assertTitle($node2->label() . ' | Drupal');

    // Create a new page entity to take over node pages.
    $edit = [
      'label' => 'Node View',
      'id' => 'node_view',
      'path' => 'node/%',
    ];
    $this->drupalPostForm('admin/structure/page_manager/add', $edit, 'Save');

    // Create a new variant to always return 404.
    $edit = [
      'id' => 'http_status_code',
      'variant_settings[status_code]' => 404,
    ];
    $this->drupalPostForm('admin/structure/page_manager/manage/node_view/add/http_status_code', $edit, 'Save');

    $this->drupalGet('node/' . $node1->id());
    $this->assertResponse(404);
    $this->assertCacheTag('page_manager_route_name:entity.node.canonical');
    $this->assertNoText($node1->label());
    $this->drupalGet('node/' . $node2->id());
    $this->assertResponse(404);
    $this->assertNoText($node2->label());

    // Add a new variant.
    $this->drupalGet('admin/structure/page_manager/manage/node_view');
    $this->clickLink('Add new variant');
    $this->clickLink('Block page');
    $edit = [
      'id' => 'block_page_first',
      'label' => 'First',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Add the entity view block.
    $this->clickLink('Add new block');
    $this->clickLink('Entity view (Content)');
    $edit = [
      'region' => 'top',
      'settings[label_display]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Add block');

    // Add a node bundle condition for articles.
    $this->clickLink('Add new selection condition');
    $this->clickLink('Node Bundle');
    $edit = [
      'condition[bundles][article]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Add selection condition');

    // Set the page title to the node title.
    $edit = [
      'variant_settings[page_title]' => '[node:title]',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    /** @var \Drupal\page_manager\PageInterface $page */
    $page = Page::load('node_view');
    foreach ($page->getVariants() as $block_variant_uuid => $block_variant) {
      if ($block_variant->label() == 'First') {
        break;
      }
    }
    // Set the weight of the block_page variant to -10.
    $edit = [
      'variants[' . $block_variant_uuid . '][weight]' => -10,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // The page node will 404, but the article node will display the variant.
    $this->drupalGet('node/' . $node1->id());
    $this->assertResponse(404);
    $this->assertNoText($node1->label());

    $this->drupalGet('node/' . $node2->id());
    $this->assertResponse(200);
    $this->assertTitle($node2->label() . ' | Drupal');
    $this->assertText($node2->body->value);

    // Test cacheability metadata.
    $this->drupalGet('node/' . $node3->id());
    $this->assertNoText($node2->label());

    // Ensure this doesn't affect the /node/add page.
    $this->drupalGet('node/add');
    $this->assertResponse(200);
  }

}
