<?php

/**
 * @file
 * Builds an FAQ page based on published FAQ content
 */

namespace Drupal\faq\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class FAQController extends ControllerBase {

    /**
     * Page cache kill switch, dependency injected from Symfony service.
     *
     * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
     */
    protected $killSwitch;

    /**
     * {@inheritdoc}
     */
    public function __construct(KillSwitch $kill_switch) {
        $this->killSwitch = $kill_switch;
    }

    /**
    * {@inheritdoc}
    */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('page_cache_kill_switch') // inject cache killswitch
        );
    }

    /**
     * Gets and returns all node Ids for published FAQ items.
     *
     * @return array|null
     */
    protected function load_list($keywords=null, $category=null) {
        try {
            // Get language to guide query
            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

            // Assemble the query
            $query = \Drupal::entityQuery('node');
            $query->accessCheck(TRUE);
            $query = $query->condition('status', 1)
                ->condition('type', 'faq');

            if (!empty($keywords) && trim($keywords) != '') {
                $group = $query->orConditionGroup()
                    ->condition('title', $keywords, 'CONTAINS', $language)
                    ->condition('body', $keywords, 'CONTAINS', $language);
                $query = $query->condition($group);
            }

            if (!empty($category) && trim($category) != '') {
                $query = $query->condition('field_faq_categories.entity:taxonomy_term.name', $category);
            }

            $entity_ids = $query->execute();
        } catch (\Exception $e) {
            \Drupal::messenger()->addStatus(
                t('Unable to access the database at this time, please try again later' . $e->getMessage())
            );
            return NULL;
        }

        return $entity_ids;
    }

    /**
     * Creates the FAQ list page
     *
     * @return array
     *   Render array for FAQ list output
     */
    public function list() {
        // Kill the cache because it causes all sorts of bugs
        $this->killSwitch->trigger();

        // Get search variables
        $request = Request::createFromGlobals();
        $keywords = trim($request->get('keywords', ''));
        $category = trim($request->get('category', ''));

        // Get published faq ids
        $faq_ids = $this->load_list($keywords, $category);

        // Get actual faq nodes
        $faq_nodes = Node::loadMultiple($faq_ids);

        // Give the nodes over to the template
        return [
            '#theme' => 'faq_list',
            '#faq_nodes' => $faq_nodes,
            '#title' => t('Frequently Asked Questions'),
            '#keywords' => $keywords,
            '#category' => $category,
        ];
    }
}
