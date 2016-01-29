<?php

/**
 * @file
 * Definition of \Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin\MediaEmbed.
 */

namespace Drupal\ckeditor_media_embed\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Media Embed" plugin.
 *
 * @CKEditorPlugin(
 *   id = "embed",
 *   label = @Translation("Media Embed"),
 *   module = "ckeditor_media_embed"
 * )
 */
class MediaEmbed extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return array(
      'embedbase',
      'notificationaggregator',
      'notification',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'ckeditor_media_embed') . '/js/plugins/embed/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'Embed' => array(
        'label' => t('Media Embed'),
        'image' => drupal_get_path('module', 'ckeditor_media_embed') . '/js/plugins/embed/icons/embed.png',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array();
  }

}
