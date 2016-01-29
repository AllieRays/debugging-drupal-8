<?php

/**
 * @file
 * Contains \Drupal\ctools\Tests\Wizard\CToolsWizardTest.
 */

namespace Drupal\ctools\Tests\Wizard;


use Drupal\simpletest\WebTestBase;

/**
 * Tests basic wizard functionality.
 *
 * @group ctools
 */
class CToolsWizardTest extends WebTestBase {

  public static $modules = array('ctools', 'ctools_wizard_test');

  function testWizardSteps() {
    $this->drupalGet('ctools/wizard');
    $this->assertText('Form One');
    $this->dumpHeaders = TRUE;
    $edit = [
      'one' => 'test',
    ];
    // First step in the wizard.
    $this->drupalPostForm('ctools/wizard', $edit, t('Next'));
    // Redirected to the second step.
    $this->assertText('Form Two');
    // Hit previous to make sure our form value are preserved.
    $this->drupalPostForm(NULL, [], t('Previous'));
    // Check the known form values.
    $this->assertFieldByName('one', 'test');
    // Goto next step again and finish this wizard.
    $this->drupalPostForm(NULL, [], t('Next'));
    $edit = [
      'two' => 'Second test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Finish'));
    // Check that the wizard finished properly.
    $this->assertText('Value One: test');
    $this->assertText('Value Two: Second test');
  }

}
