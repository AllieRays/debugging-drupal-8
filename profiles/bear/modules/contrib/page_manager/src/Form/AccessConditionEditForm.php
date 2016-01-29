<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\AccessConditionEditForm.
 */

namespace Drupal\page_manager\Form;

/**
 * Provides a form for editing an access condition.
 */
class AccessConditionEditForm extends AccessConditionFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'page_manager_access_condition_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    // Load the access condition directly from the page entity.
    return $this->page->getAccessCondition($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update access condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label access condition has been updated.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
