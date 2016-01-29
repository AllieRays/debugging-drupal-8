<?php

/**
 * @file
 * Definition of Drupal\field_group\Tests\EntityDisplayTest.
 */

namespace Drupal\field_group\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for displaying entities.
 *
 * @group field_group
 */
class EntityDisplayTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'field_ui', 'field_group');

  function setUp() {

    parent::setUp();

    // Create test user.
    $admin_user = $this->drupalCreateUser(array('access content', 'administer content types', 'administer node fields', 'administer node form display', 'administer node display', 'bypass node access'));
    $this->drupalLogin($admin_user);

    // Create content type, with underscores.
    $type_name = strtolower($this->randomMachineName(8)) . '_test';
    $type = $this->drupalCreateContentType(array('name' => $type_name, 'type' => $type_name));
    $this->type = $type->type;

  }

  /**
   * Test if an empty  formatter.
   */
  function testFieldAccess() {

    $data = array(
      'label' => 'Wrapper',
      'weight' => '1',
      'children' => array(
        0 => 'field_no_access',
      ),
      'format_type' => 'div',
      'format_settings' => array(
        'label' => 'Link',
        'instance_settings' => array(
          'required_fields' => 0,
          'id' => 'wrapper-id',
          'classes' => 'test-class',
          'description' => '',
          'show_label' => FALSE,
          'label_element' => 'h3',
          'effect' => 'blink',
          'speed' => 'fast',
        ),
        'formatter' => 'open',
      ),
    );
    $group = $this->createGroup('default', $data);

    $groups = field_group_info_groups('node', 'article', 'default', TRUE);
    $this->drupalGet('node/' . $this->node->nid);

    // Test if group is not shown.
    $this->assertNoFieldByXPath("//div[contains(@id, 'wrapper-id')]", NULL, t('Div that contains fields with no access is not shown.'));
  }

  /**
   * Test the div formatter.
   */
  function testDiv() {

    $data = array(
      'label' => 'Wrapper',
      'weight' => '1',
      'children' => array(
        0 => 'field_test',
      ),
      'format_type' => 'div',
      'format_settings' => array(
        'label' => 'Link',
        'instance_settings' => array(
          'required_fields' => 0,
          'id' => 'wrapper-id',
          'classes' => 'test-class',
          'description' => '',
          'show_label' => FALSE,
          'label_element' => 'h3',
          'effect' => 'blink',
          'speed' => 'fast',
        ),
        'formatter' => 'open',
      ),
    );
    $group = $this->createGroup('default', $data);

    $groups = field_group_info_groups('node', 'article', 'default', TRUE);
    $this->drupalGet('node/' . $this->node->nid);

    // Test group ids and classes.
    $this->assertFieldByXPath("//div[contains(@id, 'wrapper-id')]", NULL, t('Wrapper id set on wrapper div'));
    $this->assertFieldByXPath("//div[contains(@class, 'test-class')]", NULL, t('Test class set on wrapper div') . 'class="' . $group->group_name . ' test-class');

    // Test group label.
    $this->assertNoRaw('<h3><span>' . $data['label'] . '</span></h3>', t('Label is not shown'));

    // Set show label to true.
    $group->data['format_settings']['instance_settings']['show_label'] = TRUE;

    drupal_write_record('field_group', $group, array('identifier'));
    $groups = field_group_info_groups('node', 'article', 'default', TRUE);
    $this->drupalGet('node/' . $this->node->nid);
    $this->assertRaw('<h3><span>' . $data['label'] . '</span></h3>', t('Label is shown'));

    // Change to collapsible
    $group->data['format_settings']['formatter'] = 'collapsible';
    drupal_write_record('field_group', $group, array('identifier'));
    $groups = field_group_info_groups('node', 'article', 'default', TRUE);
    $this->drupalGet('node/' . $this->node->nid);
    $this->assertFieldByXPath("//div[contains(@class, 'speed-fast')]", NULL, t('Speed class is set'));
    $this->assertFieldByXPath("//div[contains(@class, 'effect-blink')]", NULL, t('Effect class is set'));
  }

  /**
   * Test the horizontal tabs formatter.
   */
  function testHorizontalTabs() {

    $data = array(
      'label' => 'Tab 1',
      'weight' => '1',
      'children' => array(
        0 => 'field_test',
      ),
      'format_type' => 'htab',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class',
          'description' => '',
        ),
        'formatter' => 'open',
      ),
    );
    $first_tab = $this->createGroup('default', $data);

    $data = array(
      'label' => 'Tab 2',
      'weight' => '1',
      'children' => array(
        0 => 'field_test_2',
      ),
      'format_type' => 'htab',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class-2',
          'description' => 'description of second tab',
        ),
        'formatter' => 'closed',
      ),
    );
    $second_tab = $this->createGroup('default', $data);

    $data = array(
      'label' => 'Tabs',
      'weight' => '1',
      'children' => array(
        0 => $first_tab->group_name,
        1 => $second_tab->group_name,
      ),
      'format_type' => 'htabs',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class-wrapper',
        ),
      ),
    );
    $tabs = $this->createGroup('default', $data);

    $groups = field_group_info_groups('node', 'article', 'default', TRUE);

    $this->drupalGet('node/' . $this->node->nid);

    // Test properties.
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]", NULL, t('Test class set on tabs wrapper'));
    $this->assertFieldByXPath("//fieldset[contains(@class, 'test-class-2')]", NULL, t('Test class set on second tab'));
    $this->assertRaw('<div class="fieldset-description">description of second tab</div>', t('Description of tab is shown'));
    $this->assertRaw('class="collapsible collapsed test-class-2', t('Second tab is default collapsed'));

    // Test if correctly nested
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//fieldset[contains(@class, 'test-class')]", NULL, 'First tab is displayed as child of the wrapper.');
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//fieldset[contains(@class, 'test-class-2')]", NULL, 'Second tab is displayed as child of the wrapper.');

  }

  /**
   * Test the vertical tabs formatter.
   */
  function testVerticalTabs() {

    $data = array(
      'label' => 'Tab 1',
      'weight' => '1',
      'children' => array(
        0 => 'field_test',
      ),
      'format_type' => 'tab',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class',
          'description' => '',
        ),
        'formatter' => 'open',
      ),
    );
    $first_tab = $this->createGroup('default', $data);
    $first_tab_id = 'edit-' . $first_tab->group_name;

    $data = array(
      'label' => 'Tab 2',
      'weight' => '1',
      'children' => array(
        0 => 'field_test_2',
      ),
      'format_type' => 'tab',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class-2',
          'description' => 'description of second tab',
        ),
        'formatter' => 'closed',
      ),
    );
    $second_tab = $this->createGroup('default', $data);
    $second_tab_id = 'edit-' . $second_tab->group_name;

    $data = array(
      'label' => 'Tabs',
      'weight' => '1',
      'children' => array(
        0 => $first_tab->group_name,
        1 => $second_tab->group_name,
      ),
      'format_type' => 'tabs',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class-wrapper',
        ),
      ),
    );
    $tabs = $this->createGroup('default', $data);

    $groups = field_group_info_groups('node', 'article', 'default', TRUE);

    $this->drupalGet('node/' . $this->node->nid);

    // Test properties.
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]", NULL, t('Test class set on tabs wrapper'));
    $this->assertFieldByXPath("//fieldset[contains(@class, 'test-class-2')]", NULL, t('Test class set on second tab'));
    $this->assertRaw('<div class="fieldset-description">description of second tab</div>', t('Description of tab is shown'));
    $this->assertRaw('class="collapsible collapsed test-class-2', t('Second tab is default collapsed'));

    // Test if correctly nested
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//fieldset[contains(@id, '$first_tab_id')]", NULL, 'First tab is displayed as child of the wrapper.');
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//fieldset[contains(@id, '$second_tab_id')]", NULL, 'Second tab is displayed as child of the wrapper.');
  }

  /**
   * Test the accordion formatter.
   */
  function testAccordion() {

    $data = array(
      'label' => 'Accordion item 1',
      'weight' => '1',
      'children' => array(
        0 => 'field_test',
      ),
      'format_type' => 'accordion-item',
      'format_settings' => array(
        'label' => 'Accordion item 1',
        'instance_settings' => array(
          'classes' => 'test-class',
        ),
        'formatter' => 'closed',
      ),
    );
    $first_item = $this->createGroup('default', $data);
    $first_item_id = 'node_article_full_' . $first_item->group_name;

    $data = array(
      'label' => 'Accordion item 2',
      'weight' => '1',
      'children' => array(
        0 => 'field_test_2',
      ),
      'format_type' => 'accordion-item',
      'format_settings' => array(
        'label' => 'Tab 2',
        'instance_settings' => array(
          'classes' => 'test-class-2',
        ),
        'formatter' => 'open',
      ),
    );
    $second_item = $this->createGroup('default', $data);
    $second_item_id = 'node_article_full_' . $second_item->group_name;

    $data = array(
      'label' => 'Accordion',
      'weight' => '1',
      'children' => array(
        0 => $first_item->group_name,
        1 => $second_item->group_name,
      ),
      'format_type' => 'accordion',
      'format_settings' => array(
        'label' => 'Tab 1',
        'instance_settings' => array(
          'classes' => 'test-class-wrapper',
          'effect' => 'bounceslide'
        ),
      ),
    );
    $accordion = $this->createGroup('default', $data);

    $groups = field_group_info_groups('node', 'article', 'default', TRUE);

    $this->drupalGet('node/' . $this->node->nid);

    // Test properties.
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]", NULL, t('Test class set on tabs wrapper'));
    $this->assertFieldByXPath("//div[contains(@class, 'effect-bounceslide')]", NULL, t('Correct effect is set on the accordion'));
    $this->assertFieldByXPath("//div[contains(@class, 'test-class')]", NULL, t('Accordion item with test-class is shown'));
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-2')]", NULL, t('Accordion item with test-class-2 is shown'));
    $this->assertFieldByXPath("//h3[contains(@class, 'field-group-accordion-active')]", NULL, t('Accordion item 2 was set active'));

    // Test if correctly nested
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//div[contains(@class, 'test-class')]", NULL, 'First item is displayed as child of the wrapper.');
    $this->assertFieldByXPath("//div[contains(@class, 'test-class-wrapper')]//div[contains(@class, 'test-class-2')]", NULL, 'Second item is displayed as child of the wrapper.');
  }

}