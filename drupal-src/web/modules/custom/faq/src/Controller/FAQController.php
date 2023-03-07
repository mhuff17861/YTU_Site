<?php

/**
 * @file
 * Builds an FAQ page based on published FAQ content
 */

 namespace Drupal\faq\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use \Drupal\node\Entity\Node;

 class FAQController extends ControllerBase {

    /**
     * Gets and returns all node Ids for published FAQ items.
     *
     * @return array|null
     */
    protected function load() {
        try {
            $query = \Drupal::entityQuery('node');
            $query->condition('status', 1)
                ->condition('type', 'faq');

            $entity_ids = $query->execute();
        } catch (\Exception $e) {
            \Drupal::messenger()->addStatus(
                t('Unable to access the database at this time, please try again later')
            );
            return NULL;
        }
    }

    /**
     * Creates the FAQ list page
     *
     * @return array
     *   Render array for FAQ list output
     */
    public function list() {
        // Get published faq ids
        $faq_ids = $this->load();

        // Get actual faq nodes
        $faq_nodes = Node::loadMultiple($faq_ids);

        // Give the nodes over to the template
        return [
            '#theme' => 'faq_list',
            '#faq_nodes' => $faq_nodes,
            '#title' => t('Frequently Asked Questions'),
        ];
    }
 }
