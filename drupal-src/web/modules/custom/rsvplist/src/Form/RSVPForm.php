<?php

/**
 * @file
 * A form to collect an email address for RSVP details.
 */

 namespace Drupal\rsvplist\Form;

 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;

 class RSVPForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'rsvplist_email_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Attempt to get the fully loaded node object of page
        $node = \Drupal::routeMatch()->getParameter('node');

        // Some pages may not be nodes and it will be null
        if (!(is_null($node))) {
            $nid = $node->id();
        } else {
            // Default to 0
            $nid = 0;
        }

        // Establish $form render array. It has an email text field,
        // a submit button, and a hidden field containing the node ID.
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email address'),
            '#size' => 25,
            '#description' => t('We will send updates to the email address you provide'),
            '#required' => true,
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('RSVP'),
        ];

        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $value = $form_state->getValue('email');

        if (!(\Drupal::service('email.validator')->isValid($value))) {
            $form_state->setErrorByNAme(
                'email',
                $this->t('It appears that email %mail is not a valid email. Please try again',
                ['%mail' => $value])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        try {
            // Get user id (without full user object. For full user object
            //, use \Drupal\user\Entity\User::load(\Drupal::currentUser()->id();))())
            $uid = \Drupal::currentUser()->id();
            $nid = $form_state->getValue('nid');
            $email = $form_state->getValue('email');
            $current_time = \Drupal::time()->getRequestTime();

            $query= \Drupal::database()->insert('rsvplist');

            $query->fields([
                'uid',
                'nid',
                'mail',
                'created',
            ]);

            $query->values([
                $uid,
                $nid,
                $email,
                $current_time,
            ]);

            $query->execute();

            $this->messenger()->addMessage(t('Thank you for your RSVP!'));
        } catch (\Exception $e) {
            $this->messenger()->addMessage(t('Unable to save RSVP, please try again another time.' . $e->getMessage()));
        }
    }
 }
