<?php
/**
 * @file
 * Contains \Drupal\ckeditor_media_embed\EmbedInterface.
 */

namespace Drupal\ckeditor_media_embed;

interface EmbedInterface {

  /**
   * Retrieve the link to the configuration page for the settings.
   */
  public function getSettingsLink();

  /**
   * Sets and marshals the specified provider url meant for CKEditor's embed
   * plugin for use in a ckeditor_media_embed Embed.
   */
  public function setEmbedProvider($provider);

  /**
   * Retrieve the Embed object as provided by the embed provider.
   *
   * @param string $url
   *   The url to the media to request an embed object for.
   *
   * @param object
   *   The decoded json object retrieved from the provided for the specified url.
   */
  public function getEmbedObject($url);

  /**
   * Replace all <oembed> tags with their embed html provided by the provider
   * resource.
   *
   * @param string $text
   *   The HTML string to replace <oembed> tags.
   *
   * @return string
   *   The HTML with all the <oembed> tags replaced with their embed html.
   */
  public function processEmbeds($text);

}
