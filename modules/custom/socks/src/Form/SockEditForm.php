<?php

/**
 * @file
 * Contains Drupal\socks\Form\SockEditForm.
 */

namespace Drupal\socks\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SockEditForm
 *
 * Provides the edit form for our Sock entity.
 *
 * @package Drupal\socks\Form
 *
 * @ingroup socks
 */
class SockEditForm extends SockFormBase {

  /**
   * Returns the actions provided by this form.
   *
   * For the edit form, we only need to change the text of the submit button.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update Sock');
    return $actions;
  }

}
