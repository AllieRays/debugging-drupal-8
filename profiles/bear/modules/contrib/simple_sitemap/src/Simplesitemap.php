<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Simplesitemap.
 */

namespace Drupal\simplesitemap;

/**
 * Simplesitemap class.
 */
class Simplesitemap {

  const SITEMAP_PLUGIN_PATH = 'src/LinkGenerators/EntityTypeLinkGenerators';

  private $config;
  private $sitemap;
  private $language;

  function __construct() {
    $this->set_current_lang();
    $this->set_config();
  }

  public static function get_form_entity($form_state) {
    if (!is_null($form_state->getFormObject()) && method_exists($form_state->getFormObject(), 'getEntity')) {
      $entity = $form_state->getFormObject()->getEntity();
      return $entity;
    }
    return FALSE;
  }

  public static function get_entity_type_name($entity) {
    if (method_exists($entity, 'getEntityType')) {
      return $entity->getEntityType()->getBundleOf();
    }
    return FALSE;
  }

  public static function get_plugin_path($entity_type_name) {
    $class_path = drupal_get_path('module', 'simplesitemap') . '/' . self::SITEMAP_PLUGIN_PATH . '/' . $entity_type_name . '.php';
    if (file_exists($class_path)) {
      return $class_path;
    }
    return FALSE;
  }

  private function set_current_lang($language = NULL) {
    $this->language = is_null($language) ? \Drupal::languageManager()->getCurrentLanguage() : $language;
  }

  private function set_config() {
    $this->get_config_from_db();
    $this->get_sitemap_from_db();
  }

  // Get sitemap from database.
  private function get_sitemap_from_db() {
    $result = db_select('simplesitemap', 's')
      ->fields('s', array('sitemap_string'))
      ->condition('language_code', $this->language->getId())
      ->execute()->fetchAll();
    $this->sitemap = !empty($result[0]->sitemap_string) ? $result[0]->sitemap_string : NULL;
  }

  // Get sitemap settings from configuration storage.
  private function get_config_from_db() {
    $this->config = \Drupal::config('simplesitemap.settings');
  }

  public function save_entity_types($entity_types) {
    $this->save_config('entity_types', $entity_types);
  }

  private function save_config($key, $value) {
    \Drupal::service('config.factory')->getEditable('simplesitemap.settings')->set($key, $value)->save();
    $this->set_config();
  }

  public function get_sitemap() {
    if (empty($this->sitemap)) {
      $this->generate_sitemap();
    }
    return $this->sitemap;
  }

  private function generate_sitemap() {
    $generator = new SitemapGenerator();
    $generator->set_sitemap_lang($this->language);
    $generator->set_custom_links($this->config->get('custom'));
    $generator->set_entity_types($this->config->get('entity_types'));
    $this->sitemap = $generator->generate_sitemap();
    $this->save_sitemap();
  }

  public function generate_all_sitemaps() {
    $generator = new SitemapGenerator();
    $generator->set_custom_links($this->config->get('custom'));
    $generator->set_entity_types($this->config->get('entity_types'));
    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      $generator->set_sitemap_lang($language);
      $this->language = $language;
      $this->sitemap = $generator->generate_sitemap();
      $this->save_sitemap();
    }
  }

  private function save_sitemap() {

    //todo: db_merge not working in D8(?), this is why the following queries are needed:
//    db_merge('simplesitemap')
//      ->key(array('language_code', $this->lang))
//      ->fields(array(
//        'language_code' => $this->lang,
//        'sitemap_string' => $this->sitemap,
//      ))
//      ->execute();
    $exists_query = db_select('simplesitemap')
      ->condition('language_code', $this->language->getId())
      ->countQuery()->execute()->fetchField();

    if ($exists_query > 0) {
      db_update('simplesitemap')
        ->fields(array(
          'sitemap_string' => $this->sitemap,
        ))
        ->condition('language_code', $this->language->getId())
        ->execute();
    }
    else {
      db_insert('simplesitemap')
        ->fields(array(
          'language_code' => $this->language->getId(),
          'sitemap_string' => $this->sitemap,
        ))
        ->execute();
    }
  }

  public function get_entity_types() {
    return $this->config->get('entity_types');
  }
}
