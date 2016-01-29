<?php

/**
 * @file
 * Contains \Drupal\ckeditor_media_embed\Form\CKEditorMediaEmbedSettingsForm.
 */

namespace Drupal\ckeditor_media_embed\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Class CKEditorMediaEmbedSettingsForm.
 *
 * @package Drupal\ckeditor_media_embed\Form
 */
class CKEditorMediaEmbedSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ckeditor_media_embed.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor_media_embed_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ckeditor_media_embed.settings');

    $form['embed_provider'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Provider URL'),
      '#default_value' => $config->get('embed_provider'),
      '#description' => $this->t('
        A template for the URL of the provider endpoint. This URL will be queried for each resource to be embedded. By default CKEditor uses the Iframely service.<br />
        Check out the <a href=":help">help</a> page for more information.<br />
        <strong>Example</strong> <code>//example.com/api/oembed-proxy?resource-url={url}&callback={callback}</code><br />
        <strong>Default</strong> <code>//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}</code><br />',
        array(':help' => \Drupal::url('help.page', array('name' => 'ckeditor_media_embed')))
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $embed_provider = $form_state->getValue('embed_provider');
    $this->prepareEmbedProviderValidation($embed_provider);
    if (!URLHelper::isValid($embed_provider, TRUE)) {
      $form_state->setErrorByName('embed_provider', $this->t('The provider url was not valid.'));
    }
  }

  /**
   * Prepare the embed provider setting for validation.
   *
   * @param string $embed_provider
   *   The embed provider that should be prepared for validation.
   *
   * @return string
   *   The embed provider url with a schema and escaped tokens so that it has
   *   a chance to validate.
   *
   * @return $this
   */
  protected function prepareEmbedProviderValidation(&$embed_provider) {
    if (strpos($embed_provider, '//') !== FALSE) {
      $embed_provider = 'http:' . $embed_provider;
    }
    $embed_provider = str_replace('{url}', '', $embed_provider);
    $embed_provider = str_replace('{callback}', '', $embed_provider);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ckeditor_media_embed.settings')
      ->set('embed_provider', $form_state->getValue('embed_provider'))
      ->save();
  }

}
