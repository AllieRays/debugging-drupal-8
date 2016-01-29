<?php

/**
 * @file
 * Contains \Drupal\field_group\Plugin\field_group\FieldGroupFormatter\HtmlElement.
 */

namespace Drupal\field_group\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Plugin implementation of the 'html_element' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "html_element",
 *   label = @Translation("Html element"),
 *   description = @Translation("This fieldgroup renders the inner content in a HTML element with classes and attributes."),
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class HtmlElement extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element) {

    $element_attributes = new \Drupal\Core\Template\Attribute();

    if ($this->getSetting('attributes')) {

      // This regex split the attributes string so that we can pass that
      // later to drupal_attributes().
      preg_match_all('/([^\s=]+)="([^"]+)"/', $this->getSetting('attributes'), $matches);

      // Put the attribute and the value together.
      foreach ($matches[1] as $key => $attribute) {
        $element_attributes[$attribute] = $matches[2][$key];
      }

    }

    // Add the classes to the attributes array.
    if ($this->getSetting('classes')) {

      if (!isset($element_attributes['class'])) {
        $element_attributes['class'] = array();
      }

      $element_attributes['class'][] = $this->getSetting('classes');

    }

    $element['#prefix'] = '<' . $this->getSetting('element') . $element_attributes . '>';
    if ($this->getSetting('show_label')) {
      $element['#prefix'] .= '<' . $this->getSetting('label_element') . '><span>';
      $element['#prefix'] .= SafeMarkup::checkPlain($this->t($group->label));
      $element['#prefix'] .= '</span></' . $this->getSetting('label_element') . '>';
    }
    $element['#suffix'] = '</' . $this->getSetting('element') . '>';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {

    $form = parent::settingsForm();

    $form['element'] = array(
      '#title' => $this->t('Element'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('element'),
      '#description' => $this->t('E.g. div, section, aside etc.'),
      '#weight' => 1,
    );

    $form['show_label'] = array(
      '#title' => $this->t('Show label'),
      '#type' => 'select',
      '#options' => array(0 => $this->t('No'), 1 => $this->t('Yes')),
      '#default_value' => $this->getSetting('show_label'),
      '#weight' => 2,
    );

    $form['label_element'] = array(
      '#title' => $this->t('Label element'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('label_element'),
      '#weight' => 3,
    );

    $form['attributes'] = array(
      '#title' => $this->t('Attributes'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('attributes'),
      '#description' => $this->t('E.g. name="anchor"'),
      '#weight' => 4,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = parent::settingsSummary();
    $summary[] = $this->t('Element: @element',
      array('@element' => $this->getSetting('element'))
    );

    if ($this->getSetting('show_label')) {
      $summary[] = $this->t('Label element: @element',
        array('@element' => $this->getSetting('label_element'))
      );
    }

    if ($this->getSetting('attributes')) {
      $summary[] = $this->t('Attributes: @attributes',
        array('@attributes' => $this->getSetting('attributes'))
      );
    }

    if ($this->getSetting('required_fields')) {
      $summary[] = $this->t('Mark as required');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'element' => 'div',
      'show_label' => 0,
      'label_element' => 'div',
      'attributes' => '',
      'required_fields' => 1,
    ) + parent::defaultSettings();
  }

}
