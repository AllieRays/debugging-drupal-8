<?php

/**
 * @file
 * Contains \Drupal\socks\Tests\KneeHighsController.
 */

namespace Drupal\socks\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the socks module.
 */
class KneeHighsControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "socks KneeHighsController's controller functionality",
      'description' => 'Test Unit for module socks and controller KneeHighsController.',
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
  public function testKneeHighsController() {
    // Check that the basic functions of module socks.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
