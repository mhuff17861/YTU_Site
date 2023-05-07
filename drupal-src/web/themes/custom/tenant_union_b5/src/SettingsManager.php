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

        // ***** Banner *****
        $form['frontpage_banner'] = [
            '#type' => 'details',
            '#title' => $this->t('Front Page Banner'),
            '#description' => $this->t('Add or update the front page banner.'),
            '#open' => TRUE,
        ];

        $form['frontpage_banner']['frontpage_banner_image'] = [
            '#type' => 'managed_file',
            '#attributes' => [
                'accept' => '.png,.jpg,.jpeg',
            ],
            '#file_validators' => [
                'file_validate_extensions' => [
                    'png jpg jpeg',
                ],
            ],
            '#upload_location' => 'public://theme_img_uploads/banner',
            '#title' => $this->t('Front Page Banner Image'),
            '#description' => $this->t("A banner image to show on the front page. Can be a .jpg, .jpeg, or .png file."),
        ];

        $form['frontpage_banner']['frontpage_banner_image_alt_text'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Front Page Banner Alt Text'),
            '#default_value' => theme_get_setting('frontpage_banner_image_alt_text'),
            '#description' => $this->t("A brief description of the banner image for accessibility purposes."),
        ];

        $form['frontpage_banner']['update'] = [
            '#type' => 'submit',
            '#name' => 'frontpage_banner_update',
            '#value' => $this->t('Update Banner'),
            '#button_type' => 'danger',
            '#attributes' => [
                'class' => ['btn btn-danger'],
            ],
            '#submit' => ['tenant_union_b5_form_system_theme_settings_frontpage_options_submit'],
        ];
    }

    /**
     * Submit callback.
     *
     * @param array $form
     *   An associative array containing the structure of the form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form.
     *
     * @see hook_form_system_theme_settings_alter()
     */
    public function submitFrontPageBannerImage(array &$form, FormStateInterface $form_state) {
        // Get the theme config
        $config = \Drupal::service('config.factory')->getEditable('tenant_union_b5.settings');

        if ($file_id = $form_state->getValue(['frontpage_banner_image', '0'])) {
            $storage = \Drupal::entityTypeManager()->getStorage('file');
            $file = $storage->load($file_id);
            if ($file != null) {
                $file->setPermanent();
                $file->save();
                $config->set('frontpage_banner_image_uri', $file->getFileUri())
                    ->save();
            }
        }

        $config->set('frontpage_banner_image_alt_text', $form_state->getValue('frontpage_banner_image_alt_text'))
            ->save();

        \Drupal::messenger()->addStatus(
            t('Front Page Banner Updated.')
        );
    }
}
