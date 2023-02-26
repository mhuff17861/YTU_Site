<?php

namespace Drupal\front_page\Controller;

class FrontPageController {

    /**
     * Constructs the front page base on given data
     */
    function content() {
        // get config
        $config = \Drupal::config('front_page.settings');

        // get theme and return with array
        return array(
            '#theme' => 'front_page',
            '#page_title' => $config->get('front_page.page_title'),
            '#subtitle' => $config->get('front_page.subtitle'),
            '#show_button' => $config->get('front_page.show_button'),
            '#button_text' => $config->get('front_page.button_text'),
            '#button_url' => $config->get('front_page.button_url'),
        );
    }
}
