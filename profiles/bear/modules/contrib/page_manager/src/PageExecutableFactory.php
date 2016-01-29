<?php

/**
 * @file
 * Contains \Drupal\page_manager\PageExecutableFactory.
 */

namespace Drupal\page_manager;

/**
 * Provides a factory for page executables.
 */
class PageExecutableFactory implements PageExecutableFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function get(PageInterface $page) {
    return new PageExecutable($page);
  }

}
