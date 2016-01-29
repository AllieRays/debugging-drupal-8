<?php
/**
 * @file
 * Contains \Drupal\simplesitemap\Controller\SimplesitemapController.
 */

namespace Drupal\simplesitemap\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\simplesitemap\Simplesitemap;

/**
 * SimplesitemapController.
 */
class SimplesitemapController {

  /**
   * Generates the sitemap.
   */
  public function get_sitemap() {

    $sitemap = new Simplesitemap;
    $output = $sitemap->get_sitemap();

    // Display sitemap with correct xml header.
    return new Response($output, Response::HTTP_OK, array('content-type' => 'application/xml'));
  }
}
