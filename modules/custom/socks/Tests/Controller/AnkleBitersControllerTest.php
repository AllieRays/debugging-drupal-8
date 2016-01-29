<?php

/**
 * @file
 * Contains \Drupal\socks\Tests\AnkleBitersController.
 */

namespace Drupal\socks\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the socks module.
 */
class AnkleBitersControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "socks AnkleBitersController's controller functionality",
      'description' => 'Test Unit for module socks and controller AnkleBitersController.',
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
  public function testAnkleBitersController() {
    // Check that the basic functions of module socks.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
