<?php

/**
 * @file
 * Builds a tenant resources page based on published tenant resource content
 */

namespace Drupal\tenant_resources\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TenantResourcesController extends ControllerBase {

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
     * Gets and returns all node Ids for published tenant resource items.
     *
     * @return array|null
     */
    protected function load_list() {
        try {
            // Get language to guide query
            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

            // Assemble the query
            $query = \Drupal::entityQuery('node');
            $query->accessCheck(TRUE);
            $query = $query->condition('status', 1)
                ->condition('type', 'tenant_resource');

            $entity_ids = $query->execute();
        } catch (\Exception $e) {
            \Drupal::messenger()->addStatus(
                t('Unable to access the database at this time, please try again later')
            );
            return NULL;
        }

        return $entity_ids;
    }

    /**
     * Creates the tenant resource list page
     *
     * @return array
     *   Render array for tenant resource list output
     */
    public function list() {
        // Get the tenant resource introduction from the admin settings
        $config = $this->config('tenant_resources.settings');
        $introduction = $config->get('introduction');

        // Get published tenant resource ids
        $tenant_resource_ids = $this->load_list();

        // Get actual tenant_resource nodes
        $tenant_resource_nodes = Node::loadMultiple($tenant_resource_ids);

        // Give the nodes over to the template
        return [
            '#theme' => 'tenant_resources_list',
            '#tenant_resource_nodes' => $tenant_resource_nodes,
            '#title' => t('Tenant Resources'),
            '#introduction' => $introduction,
        ];
    }
}
