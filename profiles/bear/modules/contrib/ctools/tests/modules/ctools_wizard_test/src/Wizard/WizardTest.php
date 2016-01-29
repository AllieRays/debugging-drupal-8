<?php

/**
 * @file
 * Contains \Drupal\ctools_wizard_test\Wizard\WizardTest.
 */

namespace Drupal\ctools_wizard_test\Wizard;


use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

class WizardTest extends FormWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getWizardLabel() {
    return $this->t('Wizard Information');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineLabel() {
    return $this->t('Wizard Test Name');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations() {
    return array(
      'one' => [
        'form' => 'Drupal\ctools_wizard_test\Form\OneForm',
        'title' => $this->t('Form One'),
      ],
      'two' => [
        'form' => 'Drupal\ctools_wizard_test\Form\TwoForm',
        'title' => $this->t('Form Two'),
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'ctools.wizard.test.step';
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    drupal_set_message($this->t('Value One: @one', ['@one' => $cached_values['one']]));
    drupal_set_message($this->t('Value Two: @two', ['@two' => $cached_values['two']]));
    parent::finish($form, $form_state);
  }

}
