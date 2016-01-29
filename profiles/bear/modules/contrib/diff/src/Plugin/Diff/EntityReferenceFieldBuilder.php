<?php

/**
 * @file
 * Contains \Drupal\diff\Plugin\Diff\EntityReferenceFieldBuilder
 */

namespace Drupal\diff\Plugin\Diff;

use Drupal\diff\FieldDiffBuilderBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldDiffBuilder(
 *   id = "entity_reference_field_diff_builder",
 *   label = @Translation("Entity Reference Field Diff"),
 *   field_types = {
 *     "entity_reference"
 *   },
 * )
 */
class EntityReferenceFieldBuilder extends FieldDiffBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items) {
    $result = array();
    // Every item from $field_items is of type FieldItemInterface.
    foreach ($field_items as $field_key => $field_item) {
      if (!$field_item->isEmpty()) {
        $values = $field_item->getValue();
        // Compare entity ids.
        if (isset($values['target_id'])) {
          $result[$field_key][] = $this->t('Entity ID: ') . $values['target_id'];
        }
      }
    }

    return $result;
  }

}
