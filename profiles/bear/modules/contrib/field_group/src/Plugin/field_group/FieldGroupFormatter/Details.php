<?php

/**
/**
 * @file
 * Contains \Drupal\field_group\Plugin\field_group\FieldGroupFormatter\Details.
 */

namespace Drupal\field_group\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Details element.
 *
 * @FieldGroupFormatter(
 *   id = "details",
 *   label = @Translation("Details"),
 *   description = @Translation("Add a details element"),
 *   supported_contexts = {
 *     "form",
 *     "view"
 *   }
 * )
 */
class Details extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element) {

    $element += array(
      '#type' => 'details',
      '#title' => SafeMarkup::checkPlain($this->t($this->getLabel())),
      '#open' => $this->getSetting('open')
    );

    if ($this->getSetting('id')) {
      $element['#id'] = Html::getId($this->getSetting('id'));
    }

    if ($this->getSetting('classes')) {
      $element += array(
        '#attributes' => array('class' => explode(' ', $this->getSetting('classes'))),
      );
    }

    if ($this->getSetting('description')) {
      $element += array(
        '#description' => $this->getSetting('description'),
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['open'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Display element open by default.'),
      '#default_value' => $this->getSetting('open'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = array();
    if ($this->getSetting('open')) {
      $summary[] = $this->t('Default state open');
    }
    else {
      $summary[] = $this->t('Default state closed');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'open' => FALSE,
    ) + parent::defaultSettings();
  }

}
