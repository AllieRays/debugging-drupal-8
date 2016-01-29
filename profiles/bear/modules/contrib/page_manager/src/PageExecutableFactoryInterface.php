<?php

/**
 * @file
 * Contains \Drupal\page_manager\PageExecutableFactoryInterface.
 */

namespace Drupal\page_manager;

/**
 * Interface implemented by factories for page executables.
 */
interface PageExecutableFactoryInterface {

  /**
   * Instantiates a PageExecutable class for the given page.
   *
   * @param \Drupal\page_manager\PageInterface $page
   *   The page entity.
   */
  public function get(PageInterface $page);

}
