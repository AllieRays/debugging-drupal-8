<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\LinkGenerators\EntityTypeLinkGenerators\taxonomy_term.
 *
 * Plugin for taxonomy term entity link generation.
 * See \Drupal\simplesitemap\LinkGenerators\CustomLinkGenerator\node for more
 * documentation.
 */

namespace Drupal\simplesitemap\LinkGenerators\EntityTypeLinkGenerators;

use Drupal\simplesitemap\LinkGenerators\EntityLinkGenerator;
use Drupal\Core\Url;

/**
 * taxonomy_term class.
 */
class taxonomy_term extends EntityLinkGenerator {

  function get_entity_bundle_links($entity_type, $bundle, $language) {

    $ids = array();
    $query = \Drupal::entityQuery($entity_type)
      ->condition('vid', $bundle);
    $ids += $query->execute();

    $urls = array();
    foreach ($ids as $id => $entity) {
      $urls[] = Url::fromRoute("entity.$entity_type.canonical", array('taxonomy_term' => $id), array(
        'language' => $language,
        'absolute' => TRUE
      ))->toString();
    }
    return $urls;
  }
}
