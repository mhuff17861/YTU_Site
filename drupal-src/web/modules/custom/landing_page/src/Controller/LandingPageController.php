<?php

namespace Drupal\landing_page\Controller;

class LandingPageController {

    /**
     * Constructs the Landing page based on given data
     */
    function content() {
        // get config
        $config = \Drupal::config('landing_page.settings');

        // get theme and return with array
        return array(
            '#theme' => 'landing_page',
            '#page_title' => $config->get('landing_page.page_title'),
            '#subtitle' => $config->get('landing_page.subtitle'),
            '#show_button' => $config->get('landing_page.show_button'),
            '#button_text' => $config->get('landing_page.button_text'),
            '#button_url' => $config->get('landing_page.button_url'),
        );
    }
}
