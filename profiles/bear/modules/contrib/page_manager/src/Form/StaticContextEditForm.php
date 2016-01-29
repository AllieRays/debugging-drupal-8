<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\StaticContextEditForm.
 */

namespace Drupal\page_manager\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\page_manager\PageInterface;

/**
 * Provides a form for adding a new static context.
 */
class StaticContextEditForm extends StaticContextFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'page_manager_static_context_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update Static Context');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label static context has been updated.', ['%label' => $this->staticContext['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PageInterface $page = NULL, $name = '') {
    $form = parent::buildForm($form, $form_state, $page, $name);
    // The machine name of an existing context is read-only.
    $form['machine_name'] = array(
      '#type' => 'value',
      '#value' => $name,
    );
    return $form;
  }

}
