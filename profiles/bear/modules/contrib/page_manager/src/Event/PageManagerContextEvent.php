<?php

/**
 * @file
 * Contains \Drupal\page_manager\Event\PageManagerContextEvent.
 */

namespace Drupal\page_manager\Event;

use Drupal\page_manager\PageExecutableInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a page entity for event subscribers.
 *
 * @see \Drupal\page_manager\Event\PageManagerEvents::PAGE_CONTEXT
 */
class PageManagerContextEvent extends Event {

  /**
   * The page the context is gathered for.
   *
   * @var \Drupal\page_manager\PageInterface
   */
  protected $page;

  /**
   * Creates a new PageManagerContextEvent.
   *
   * @param \Drupal\page_manager\PageExecutableInterface $page
   *   The page executable.
   */
  public function __construct(PageExecutableInterface $page) {
    $this->page = $page;
  }

  /**
   * Returns the page executable for this event.
   *
   * @return \Drupal\page_manager\PageExecutable
   *   The page executable.
   */
  public function getPageExecutable() {
    return $this->page;
  }

}
