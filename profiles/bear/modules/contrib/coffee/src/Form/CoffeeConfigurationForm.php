<?php

/**
 * @file
 * Contains \Drupal\coffee\Form\CoffeeConfigurationForm.
 */

namespace Drupal\coffee\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\String;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Configure Coffee for this site.
 */
class CoffeeConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'coffee_configuration_form';
  }

  /**
   * Implements Drupal\Core\Form\ConfigFormBaseTrait::getEditableConfigNames()
   *
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return array(
      'coffee.configuration',
    );
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('coffee.configuration');

    $menus = menu_ui_get_menus();

    if (!empty($menus)) {
      // Settings for coffee.
      $form['coffee_menus'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Menus to include'),
        '#description' => t('Select the menus that should be used by Coffee to search.'),
        '#options' => $menus,
        '#required' => TRUE,
        '#default_value' => (array) $config->get('coffee_menus'),
      );
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('coffee.configuration')
    ->set('coffee_menus', $form_state->getValue('coffee_menus'))
    ->save();

    parent::submitForm($form, $form_state);
    // @todo Implement Cache::invalidateTags().
  }

}
