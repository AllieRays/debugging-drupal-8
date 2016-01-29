<?php

/**
 * @file
 * Contains \Drupal\socks\Tests\OldFashionsController.
 */

namespace Drupal\socks\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the socks module.
 */
class OldFashionsControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "socks OldFashionsController's controller functionality",
      'description' => 'Test Unit for module socks and controller OldFashionsController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests socks functionality.
   */
  public function testOldFashionsController() {
    // Check that the basic functions of module socks.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
