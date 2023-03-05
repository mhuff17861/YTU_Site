<?php

namespace Drupal\landing_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LandingPageForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'landing_page_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    // Settings
    $config  = $this->config('landing_page.settings');

    // Source fields
    $form['page_title'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Front page title:'),
        '#default_value' => $config->get('age_title'),
        '#description' => $this->t('Give the front page a title.'),
    );

    $form['subtitle'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Front page subtitle:'),
        '#default_value' => $config->get('subtitle'),
        '#description' => $this->t('Give the front page a subtitle.'),
    );

    $form['show_button'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Show a button on the banner?:'),
        '#default_value' => $config->get('show_button'),
        '#description' => $this->t('Whether to show a button on the front page banner.'),
    );

    $form['button_text'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Button text:'),
        '#default_value' => $config->get('button_text'),
        '#description' => $this->t('Text to place on the button.'),
    );

    $form['button_url'] = array(
        '#type' => 'url',
        '#title' => $this->t('Button URL:'),
        '#default_value' => $config->get('button_url'),
        '#description' => $this->t('Url for the front page button.'),
    );

    // $form['banner_image'] = array(
    //     '#type' => 'managed_file',
    //     '#title' => $this->t('Banner Image:'),
    //     '#upload_validators' => array(
    //         'file_validate_extensions' => array('gif png jpg jpeg'),
    //         'file_validate_size' => array(25600000),
    //     ),
    //     '#description' => $this->t('Set the image that shows up behind the title and subtitle.'),
    // );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the values
    $config = \Drupal::configFactory()->getEditable('landing_page.settings');
    $config->set('page_title', $form_state->getValue('page_title'));
    $config->set('subtitle', $form_state->getValue('subtitle'));
    $config->set('show_button', $form_state->getValue('show_button'));
    $config->set('button_text', $form_state->getValue('button_text'));
    $config->set('button_url', $form_state->getValue('button_url'));
    // $config->set('banner_image', $form_state->getValue('banner_image'));

    // Save the values
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'landing_page.settings',
    ];
  }

}
