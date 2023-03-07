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
            '#page_title' => $config->get('page_title'),
            '#subtitle' => $config->get('subtitle'),
            '#show_button' => $config->get('show_button'),
            '#button_text' => $config->get('button_text'),
            '#button_url' => $config->get('button_url'),
        );
    }
}
