<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\SitemapGenerator.
 *
 * Generates a sitemap for entities and custom links.
 */

namespace Drupal\simplesitemap;

use Drupal\simplesitemap\LinkGenerators\CustomLinkGenerator;

/**
 * SitemapGenerator class.
 */
class SitemapGenerator {

  const PRIORITY_DEFAULT = 0.5;
  const PRIORITY_HIGHEST = 10;
  const PRIORITY_DIVIDER = 10;
  const XML_VERSION = '1.0';
  const ENCODING = 'UTF-8';
  const XMLNS_URL = 'http://www.sitemaps.org/schemas/sitemap/0.9';

  private $entity_types;
  private $custom;
  private $language;
  private $links;

  public static function get_priority_select_values() {
    $options = array();
    foreach(range(0, self::PRIORITY_HIGHEST) as $value) {
      $value = $value / self::PRIORITY_DIVIDER;
      $options[(string)$value] = (string)$value;
    }
    return $options;
  }

  public static function add_xml_link_markup($url, $priority) {
    return "<url><loc>" . $url . "</loc><priority>" . $priority . "</priority></url>";
  }

  public function set_entity_types($entity_types) {
    $this->entity_types = $entity_types;
  }

  public function set_custom_links($custom) {
    $this->custom = $custom;
  }

  public function set_sitemap_lang($language) {

    // Reset links array to make space for a sitemap with a different language.
    $this->links = array();

    $this->language = $language;
  }

  public function generate_sitemap() {

    $this->generate_custom_links();
    $this->generate_entity_links();
    $sitemap = implode($this->links);
    return $this->add_xml_sitemap_markup($sitemap);
  }

  // Add custom links.
  private function generate_custom_links() {
    $link_generator = new CustomLinkGenerator();
    $links = $link_generator->get_custom_links($this->custom , $this->language);
    $this->links = array_merge($this->links, $links);
  }

  // Add entity type links.
  private function generate_entity_links() {
    foreach($this->entity_types as $entity_type => $bundles) {
      $class_path = Simplesitemap::get_plugin_path($entity_type);
      if ($class_path !== FALSE) {
        require_once $class_path;
        $class_name = "Drupal\\simplesitemap\\LinkGenerators\\EntityTypeLinkGenerators\\$entity_type";
        $link_generator = new $class_name();
        $links = $link_generator->get_entity_links($entity_type, $bundles, $this->language);
        $this->links = array_merge($this->links, $links);
      }
    }
  }

  // Add sitemap markup.
  private function add_xml_sitemap_markup($sitemap) {
    return "<?xml version=\"" . self::XML_VERSION . "\" encoding=\"" . self::ENCODING . "\"?><urlset xmlns=\"" . self::XMLNS_URL . "\">" . $sitemap  . "</urlset>";
  }
}
