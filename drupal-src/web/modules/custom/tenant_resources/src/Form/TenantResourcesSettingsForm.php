<?php

/**
 * @file
 * Contains the settings for administering the RSVP Form
 */

namespace Drupal\tenant_resources\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TenantResourcesSettingsForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'tenant_resources_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'tenant_resources.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('tenant_resources.settings');
        $form['tenant_resources_introduction'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Tenant Resources Page Introduction'),
            '#default_value' => $config->get('introduction'),
            '#description' => $this->t('What you write here will be the first thing displayed on the Tenant Resources page.'),
        ];

        //ConfigFormBase assumes submit button for you

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $intro_text = $form_state->getValue('tenant_resources_introduction');

        $this->config('tenant_resources.settings')
            ->set('introduction', $intro_text)
            ->save();

        parent::submitForm($form,$form_state);
    }
}
