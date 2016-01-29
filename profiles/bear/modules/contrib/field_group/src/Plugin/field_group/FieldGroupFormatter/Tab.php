<?php

/**
 * @file
 * Contains \Drupal\field_group\Plugin\field_group\FieldGroupFormatter\VerticalTab.
 */

namespace Drupal\field_group\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Plugin implementation of the 'tab' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "tab",
 *   label = @Translation("Tab"),
 *   description = @Translation("This fieldgroup renders the content as a tab."),
 *   format_types = {
 *     "open",
 *     "closed",
 *   },
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   },
 * )
 */
class Tab extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element) {

    $add = array(
      '#type' => 'details',
      '#id' => 'edit-' . $this->group->group_name,
      '#title' => SafeMarkup::checkPlain($this->t($this->getLabel())),
      '#description' => $this->getSetting('description'),
    );

    if ($this->getSetting('classes')) {
      $element['#attributes']['class'] = explode(' ', $this->getSetting('classes'));
    }

    // Front-end and back-end on configuration will lead
    // to vertical tabs nested in a separate vertical group.
    if (!empty($this->group->parent_name)) {
      $add['#group'] = $this->group->parent_name;
      $add['#parents'] = array($add['#group']);
    }

    $element += $add;

  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {

    $form = parent::settingsForm();

    $form['formatter'] = array(
      '#title' => $this->t('Default state'),
      '#type' => 'select',
      '#options' => array_combine($this->pluginDefinition['format_types'], $this->pluginDefinition['format_types']),
      '#default_value' => $this->getSetting('formatter'),
      '#weight' => -4,
    );

    if ($this->context == 'form') {
      $form['required_fields'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Mark group as required if it contains required fields.'),
        '#default_value' => $this->getSetting('required_fields'),
        '#weight' => 2,
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'formatter' => 'closed',
      'description' => '',
      'required_fields' => 1,
    ) + parent::defaultSettings();
  }

}
