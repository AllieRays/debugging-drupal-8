<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGenerators\EntityLinkGenerator.
 *
 * Abstract class to be extended for plugin creation.
 * See \Drupal\simplesitemap\LinkGenerators\CustomLinkGenerator\node for more
 * documentation.
 */

namespace Drupal\simplesitemap\LinkGenerators;
use Drupal\simplesitemap\SitemapGenerator;

/**
 * EntityLinkGenerator abstract class.
 */
abstract class EntityLinkGenerator {

  private $entity_links = array();

  public function get_entity_links($entity_type, $bundles, $language) {
    foreach($bundles as $bundle => $bundle_settings) {
      if (!$bundle_settings['index']) {
        continue;
      }
      $links = $this->get_entity_bundle_links($entity_type, $bundle, $language);

      foreach ($links as &$link) {
        $link = SitemapGenerator::add_xml_link_markup($link, $bundle_settings['priority']);
      }
      $this->entity_links = array_merge($this->entity_links, $links);
    }
    return $this->entity_links;
  }

  abstract function get_entity_bundle_links($entity_type, $bundle, $language);
}
