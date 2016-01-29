<?php

/**
 * @file
 * Contains \Drupal\page_manager\Tests\PageManagerAdminTest.
 */

namespace Drupal\page_manager\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;
use Drupal\page_manager\Entity\Page;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the admin UI for page entities.
 *
 * @group page_manager
 */
class PageManagerAdminTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'page_manager', 'page_manager_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('system_branding_block');
    $this->drupalPlaceBlock('page_title_block');

    \Drupal::service('theme_handler')->install(['bartik', 'classy']);
    $this->config('system.theme')->set('admin', 'classy')->save();

    $this->drupalLogin($this->drupalCreateUser(['administer pages', 'access administration pages', 'view the administration theme']));

    // Remove the default node_view page to start with a clean UI.
    Page::load('node_view')->delete();
  }

  /**
   * Tests the Page Manager admin UI.
   */
  public function testAdmin() {
    $this->doTestAddPage();
    $this->doTestDisablePage();
    $this->doTestAddVariant();
    $this->doTestAddBlock();
    $this->doTestEditBlock();
    $this->doTestEditVariant();
    $this->doTestReorderVariants();
    $this->doTestAddPageWithDuplicatePath();
    $this->doTestAdminPath();
    $this->doTestRemoveVariant();
    $this->doTestRemoveBlock();
    $this->doTestAddBlockWithAjax();
    $this->doTestEditBlock();
    $this->doTestExistingPathWithoutParameters();
    $this->doTestDeletePage();
  }

  /**
   * Tests adding a page.
   */
  protected function doTestAddPage() {
    $this->drupalGet('admin/structure');
    $this->clickLink('Pages');
    $this->assertText('Add a new page.');

    // Add a new page without a label.
    $this->clickLink('Add page');
    $edit = [
      'id' => 'foo',
      'path' => 'admin/foo',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertText('Label field is required.');

    // Add a new page with a label.
    $edit += ['label' => 'Foo'];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertRaw(new FormattableMarkup('The %label page has been added.', ['%label' => 'Foo']));

    // Test that it is available immediately.
    $this->drupalGet('admin/foo');
    $this->assertResponse(404);
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Add new variant');
    $this->clickLink('HTTP status code');
    $edit = [
      'id' => 'http_status_code',
      'label' => 'Default',
      'variant_settings[status_code]' => 200,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $this->assertTitle('Foo | Drupal');
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    $this->drupalPostForm(NULL, ['variant_settings[status_code]' => 403], 'Save');

    // Set the weight of the 'Default' variant to 10.
    $default_variant = $this->findVariantByLabel('foo', 'Default');
    $edit = [
      'variants[' . $default_variant->id() . '][weight]' => 10,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Assert that a variant was added by default.
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->assertNoText('There are no variants.');
  }

  /**
   * Tests disabling a page.
   */
  protected function doTestDisablePage() {
    $this->drupalGet('admin/foo');
    $this->assertResponse(403);

    $this->drupalGet('admin/structure/page_manager');
    $this->clickLink('Disable');
    $this->drupalGet('admin/foo');
    // The page should not be found if the page is enabled.
    $this->assertResponse(404);

    $this->drupalGet('admin/structure/page_manager');
    $this->clickLink('Enable');
    $this->drupalGet('admin/foo');
    // Re-enabling the page should make this path available.
    $this->assertResponse(403);
  }

  /**
   * Tests adding a variant.
   */
  protected function doTestAddVariant() {
    $this->drupalGet('admin/structure/page_manager/manage/foo');

    // Add a new variant.
    $this->clickLink('Add new variant');
    $this->clickLink('Block page');
    $edit = [
      'label' => 'First',
      'id' => 'block_page',
      'variant_settings[page_title]' => 'Example title',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertRaw(new FormattableMarkup('Saved the %label variant.', ['%label' => 'First']));

    // Test that the variant is still used but empty.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    // Tests that the content region has no content at all.
    $elements = $this->xpath('//div[@class=:region]', [':region' => 'region region-content']);
    $this->assertIdentical(0, $elements[0]->count());
  }

  /**
   * Tests adding a block to a variant.
   */
  protected function doTestAddBlock() {
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    // Add a block to the variant.
    $this->clickLink('Add new block');
    $this->clickLink('User account menu');
    $edit = [
      'region' => 'top',
    ];
    $this->drupalPostForm(NULL, $edit, 'Add block');

    // Test that the block is displayed.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $elements = $this->xpath('//div[@class="block-region-top"]/nav/ul[@class="menu"]/li/a');
    $this->assertTitle('Example title | Drupal');
    $expected = ['My account', 'Log out'];
    $links = [];
    foreach ($elements as $element) {
      $links[] = (string) $element;
    }
    $this->assertEqual($expected, $links);
    // @todo Restore the <h2> check once the follow-up to
    //   https://www.drupal.org/node/1869476 is in.
    //$this->assertRaw('<h2>User account menu</h2>');
    // Check the block label.
    $this->assertRaw('User account menu');
  }

  /**
   * Tests editing a block.
   */
  protected function doTestEditBlock() {
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    $this->clickLink('Edit');
    $edit = [
      'settings[label]' => 'Updated block label',
    ];
    $this->drupalPostForm(NULL, $edit, 'Update block');
    // Test that the block is displayed.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    // Check the block label.
    // @todo Restore the <h2> check once the follow-up to
    //   https://www.drupal.org/node/1869476 is in.
    //$this->assertRaw('<h2>' . $edit['settings[label]'] . '</h2>');
    $this->assertRaw($edit['settings[label]']);
  }

  /**
   * Tests editing a variant.
   */
  protected function doTestEditVariant() {
    if (!$block = $this->findBlockByLabel('foo', 'First', 'Updated block label')) {
      $this->fail('Block not found');
      return;
    }

    $block_config = $block->getConfiguration();
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    $this->assertTitle('Edit First variant | Drupal');

    $this->assertOptionSelected('edit-variant-plugin-blocks-' . $block_config['uuid'] . '-region', 'top');
    $this->assertOptionSelected('edit-variant-plugin-blocks-' . $block_config['uuid'] . '-weight', 0);

    $form_name = 'variant_plugin[blocks][' . $block_config['uuid'] . ']';
    $edit = [
      $form_name . '[region]' => 'bottom',
      $form_name . '[weight]' => -10,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertRaw(new FormattableMarkup('Saved the %label variant.', ['%label' => 'First']));
    $this->clickLink('Edit');
    $this->assertOptionSelected('edit-variant-plugin-blocks-' . $block_config['uuid'] . '-region', 'bottom');
    $this->assertOptionSelected('edit-variant-plugin-blocks-' . $block_config['uuid'] . '-weight', -10);
  }

  /**
   * Tests reordering variants.
   */
  protected function doTestReorderVariants() {
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $elements = $this->xpath('//div[@class="block-region-bottom"]/nav/ul[@class="menu"]/li/a');
    $expected = ['My account', 'Log out'];
    $links = [];
    foreach ($elements as $element) {
      $links[] = (string) $element;
    }
    $this->assertEqual($expected, $links);

    $variant_entity = $this->findVariantByLabel('foo', 'Default');
    $edit = [
      'variants[' . $variant_entity->id() . '][weight]' => -10,
    ];
    $this->drupalPostForm('admin/structure/page_manager/manage/foo', $edit, 'Save');
    $this->drupalGet('admin/foo');
    $this->assertResponse(403);
  }

  /**
   * Tests adding a page with a duplicate path.
   */
  protected function doTestAddPageWithDuplicatePath() {
    // Try to add a second page with the same path.
    $edit = [
      'label' => 'Bar',
      'id' => 'bar',
      'path' => 'admin/foo',
    ];
    $this->drupalPostForm('admin/structure/page_manager/add', $edit, 'Save');
    $this->assertText('The page path must be unique.');
    $this->drupalGet('admin/structure/page_manager');
    $this->assertNoText('Bar');
  }

  /**
   * Tests changing the admin theme of a page.
   */
  protected function doTestAdminPath() {
    $this->config('system.theme')->set('default', 'bartik')->save();
    $this->drupalGet('admin/foo');
    $this->assertTheme('classy');

    $edit = [
      'use_admin_theme' => FALSE,
    ];
    $this->drupalPostForm('admin/structure/page_manager/manage/foo', $edit, 'Save');
    $this->drupalGet('admin/foo');
    $this->assertTheme('bartik');

    // Reset theme.
    $this->config('system.theme')->set('default', 'classy')->save();
  }

  /**
   * Tests removing a variant.
   */
  protected function doTestRemoveVariant() {
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Delete');
    $this->assertRaw(new FormattableMarkup('Are you sure you want to delete %label?', ['%label' => 'Default']));
    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertRaw(new FormattableMarkup('The variant %label has been removed.', ['%label' => 'Default']));
  }

  /**
   * Tests removing a block.
   */
  protected function doTestRemoveBlock() {
    // Assert that the block is displayed.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $elements = $this->xpath('//div[@class="block-region-bottom"]/nav/ul[@class="menu"]/li/a');
    $expected = ['My account', 'Log out'];
    $links = [];
    foreach ($elements as $element) {
      $links[] = (string) $element;
    }
    $this->assertEqual($expected, $links);

    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    $this->clickLink('Delete');
    $this->assertRaw(new FormattableMarkup('Are you sure you want to delete the block %label?', ['%label' => 'Updated block label']));
    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertRaw(new FormattableMarkup('The block %label has been removed.', ['%label' => 'Updated block label']));

    // Assert that the block is now gone.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $elements = $this->xpath('//div[@class="block-region-bottom"]/nav/ul[@class="menu"]/li/a');
    $this->assertTrue(empty($elements));
  }

  /**
   * Tests adding a block with #ajax to a variant.
   */
  protected function doTestAddBlockWithAjax() {
    $this->drupalGet('admin/structure/page_manager/manage/foo');
    $this->clickLink('Edit');
    // Add a block to the variant.
    $this->clickLink('Add new block');
    $this->clickLink('Page Manager Test Block');
    $edit = [
      'region' => 'top',
    ];
    $this->drupalPostForm(NULL, $edit, 'Add block');

    // Test that the block is displayed.
    $this->drupalGet('admin/foo');
    $this->assertResponse(200);
    $this->assertText(t('Example output'));
    // @todo Restore the <h2> check once the follow-up to
    //   https://www.drupal.org/node/1869476 is in.
    //$this->assertRaw('<h2>Page Manager Test Block</h2>');
    // Check the block label.
    $this->assertRaw('Page Manager Test Block');
  }

  /**
   * Tests adding a page with an existing path with no route parameters.
   */
  protected function doTestExistingPathWithoutParameters() {
    // Test an existing path.
    $this->drupalGet('admin');
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/page_manager');
    // Add a new page with existing path 'admin'.
    $this->clickLink('Add page');
    $edit = [
      'label' => 'existing',
      'id' => 'existing',
      'path' => 'admin',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Regular result is displayed.
    $this->assertText('The existing page has been added');

    $this->clickLink('Add new variant');
    $this->clickLink('HTTP status code');
    $edit = [
      'id' => 'http_status_code',
      'label' => 'Default',
      'variant_settings[status_code]' => 404,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Ensure the existing path leads to the new page.
    $this->drupalGet('admin');
    $this->assertResponse(404);
  }

  /**
   * Tests deleting a page.
   */
  protected function doTestDeletePage() {
    $this->drupalGet('admin/structure/page_manager');
    $this->clickLink('Delete');
    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertRaw(new FormattableMarkup('The page %name has been removed.', ['%name' => 'existing']));
    $this->drupalGet('admin');
    // The overridden page is back to its default.
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/page_manager');
    $this->clickLink('Delete');
    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertRaw(new FormattableMarkup('The page %name has been removed.', ['%name' => 'Foo']));
    $this->drupalGet('admin/foo');
    // The custom page is no longer found.
    $this->assertResponse(404);
  }

  /**
   * Tests that default arguments are not removed from existing routes.
   */
  public function testExistingRoutes() {
    // Test that the page without placeholder is accessible.
    $edit = [
      'label' => 'Placeholder test 2',
      'id' => 'placeholder2',
      'path' => '/page-manager-test',
    ];
    $this->drupalPostForm('admin/structure/page_manager/add', $edit, 'Save');
    $this->drupalGet('page-manager-test');
    // Without a single variant, it will fall through to the original.
    $this->assertResponse(200);

    $this->drupalGet('admin/structure/page_manager/manage/placeholder2');
    $this->clickLink('Add new variant');
    $this->clickLink('HTTP status code');
    $edit = [
      'id' => 'http_status_code',
      'label' => 'Default',
      'variant_settings[status_code]' => 404,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->drupalGet('page-manager-test');
    $this->assertResponse(404);

    // Test that the page test is accessible.
    $page_string = 'test-page';
    $this->drupalGet('page-manager-test/' . $page_string);
    $this->assertResponse(200);
  }

  /**
   * Asserts that a theme was used for the page.
   *
   * @param string $theme_name
   *   The theme name.
   */
  protected function assertTheme($theme_name) {
    $url = Url::fromUri('base:core/themes/' . $theme_name . '/logo.svg', ['absolute' => TRUE])->toString();
    $elements = $this->xpath('//img[@src=:url]', [':url' => $url]);
    $this->assertEqual(count($elements), 1, new FormattableMarkup('Page is rendered in @theme', ['@theme' => $theme_name]));
  }

  /**
   * Finds a block based on its page, variant, and block label.
   *
   * @param string $page_id
   *   The ID of the page entity.
   * @param string $variant_label
   *   The label of the variant.
   * @param string $block_label
   *   The label of the block.
   *
   * @return \Drupal\Core\Block\BlockPluginInterface|null
   *   Either a block plugin, or NULL.
   */
  protected function findBlockByLabel($page_id, $variant_label, $block_label) {
    if ($page_variant = $this->findVariantByLabel($page_id, $variant_label)) {
      /** @var \Drupal\ctools\Plugin\BlockVariantInterface $variant_plugin */
      $variant_plugin = $page_variant->getVariantPlugin();
      foreach ($variant_plugin->getRegionAssignments() as $blocks) {
        /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
        foreach ($blocks as $block) {
          if ($block->label() == $block_label) {
            return $block;
          }
        }
      }
    }
    return NULL;
  }

  /**
   * Finds a variant based on its page and variant label.
   *
   * @param string $page_id
   *   The ID of the page entity.
   * @param string $variant_label
   *   The label of the variant.
   *
   * @return \Drupal\page_manager\PageVariantInterface|NULL
   *   Either a variant, or NULL.
   */
  protected function findVariantByLabel($page_id, $variant_label) {
    if ($page = Page::load($page_id)) {
      /** @var \Drupal\page_manager\PageInterface $page */
      foreach ($page->getVariants() as $page_variant) {
        if ($page_variant->label() == $variant_label) {
          return $page_variant;
        }
      }
    }
    return NULL;
  }

}
