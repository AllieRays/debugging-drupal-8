<?php

/**
 * @file
 * Contains \Drupal\diff\Plugin\Diff\TermReferenceFieldBuilder
 */

namespace Drupal\diff\Plugin\Diff;

use Drupal\diff\FieldDiffBuilderBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldDiffBuilder(
 *   id = "term_reference_field_diff_builder",
 *   label = @Translation("Term Reference Field Diff"),
 *   field_types = {
 *     "taxonomy_term_reference"
 *   },
 * )
 */
class TermReferenceFieldBuilder extends FieldDiffBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items) {
    $result = array();
    // Every item from $field_items is of type FieldItemInterface.
    foreach ($field_items as $field_key => $field_item) {
      // Build the array for comparison only if the field is not empty.
      if (!$field_item->isEmpty()) {
        $values = $field_item->getValue();
        if (isset($values['target_id'])) {
          // Show term name.
          if ($this->configuration['show_name']) {
            $controller = $this->entityManager->getStorage('taxonomy_term');
            $taxonomy_term = $controller->load($values['target_id']);
            if ($taxonomy_term != NULL) {
              $result[$field_key][] = $this->t('Term name: ') . $taxonomy_term->getName();
            }
          }
          // Show term ids.
          if ($this->configuration['show_id']) {
            $result[$field_key][] = $this->t('Term id: ') . $values['target_id'];
          }
        }

        $result[$field_key] = implode('; ', $result[$field_key]);
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['show_name'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show term name'),
      '#default_value' => $this->configuration['show_name'],
    );
    $form['show_id'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show term ID'),
      '#default_value' => $this->configuration['show_id'],
    );

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['show_name'] = $form_state->getValue('show_name');
    $this->configuration['show_id'] = $form_state->getValue('show_id');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_configuration = array(
      'show_name' => 1,
      'show_id' => 0,
    );
    $default_configuration += parent::defaultConfiguration();

    return $default_configuration;
  }

}
