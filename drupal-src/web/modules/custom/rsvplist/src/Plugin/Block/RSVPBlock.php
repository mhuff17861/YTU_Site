<?php

/**
 * @file
 * Creates a block which displays the RSVPForm contained in RSVPForm.php
*/

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides the RSVP main block.
 *
 * @Block(
 *   id = "rsvp_block",
 *   admin_label = @Translation("The RSVP Block")
 * )
 */
class RSVPBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {

        // return [
        //     '#type' => 'markup',
        //     '#markup' => $this->t('My RSVP List Block'),
        // ];

        // Using the namespace!
        return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
    }

    /**
     * {@inheritdoc}
     */
    public function blockAccess(AccountInterface $account) {
        // If viewing a block on a node, get fully loaded node object
        // Only event nodes can be RSVP'd to
        $node = \Drupal::routeMatch()->getParameter('node');

        if (!(is_null($node))) {
            return AccessResult::allowedIfHasPermission($account, 'view rsvplist');
        }

        return AccessResult::forbidden();
    }
}
