<?php

/**
 * @file
 * Contains \Drupal\socks\SockAccessController.
 */

namespace Drupal\socks;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the sock entity.
 *
 * We set this class to be the access controller in Sock's entity annotation.
 *
 * @see \Drupal\socks\Entity\Sock
 *
 * @ingroup socks
 */
class SockAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // The $opereration parameter tells you what sort of operation access is
    // being checked for.
    if ($operation == 'view') {
      return TRUE;
    }
    // Other than the view operation, we're going to be insanely lax about
    // access. Don't try this at home!
    return parent::checkAccess($entity, $operation, $account);
  }

}
