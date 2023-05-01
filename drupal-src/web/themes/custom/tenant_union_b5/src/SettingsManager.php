<?php

namespace Drupal\tenant_union_b5;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Tenant Union B5 theme settings manager.
 */
class SettingsManager {

    use StringTranslationTrait;

    /**
     * The theme manager.
     *
     * @var \Drupal\Core\Theme\ThemeManagerInterface
     */
    protected $themeManager;

    /**
     * Constructs a WebformThemeManager object.
     *
     * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
     *   The theme manager.
     */
    public function __construct(ThemeManagerInterface $theme_manager) {
        $this->themeManager = $theme_manager;
    }

    /**
     * Alters theme settings form.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form.
     * @param string $form_id
     *   The form id.
     *
     * @see hook_form_system_theme_settings_alter
     */
    public function themeSettingsAlter(array &$form, FormStateInterface $form_state, $form_id) {
        // Work-around for a core bug affecting admin themes. See issue #943212.
        if (isset($form_id)) {
            return;
        }

        $form['frontpage_options'] = [
            '#type' => 'details',
            '#title' => $this->t('Front Page Options'),
            '#description' => $this->t('Options for what to display on the frontpage'),
            '#open' => TRUE,
        ];

        $form['frontpage_options']['frontpage_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Front Page Title'),
            '#default_value' => theme_get_setting('frontpage_title'),
            '#description' => $this->t("Title to display on the front page."),
        ];

        $form['frontpage_options']['frontpage_tagline'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Front Page Tagline'),
            '#default_value' => theme_get_setting('frontpage_tagline'),
            '#description' => $this->t("Tagline to display under the title on the front page."),
        ];

        $form['frontpage_options']['frontpage_show_button'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Show front page button'),
            '#default_value' => theme_get_setting('frontpage_show_button'),
            '#description' => $this->t("Show a button under the tagline on the front page."),
        ];

        $form['frontpage_options']['frontpage_button_text'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Front Page Button Text'),
            '#default_value' => theme_get_setting('frontpage_button_text'),
        ];

        $form['frontpage_options']['frontpage_button_link'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Front Page Button Link'),
            '#default_value' => theme_get_setting('frontpage_button_link'),
            '#description' => $this->t("Where the button on the front page should link to. For example: /example/page for internal links, https://example.com for external links."),
        ];
    }

}
