<?php

/**
 * @file
 * Contains \Drupal\chm\Form\ChmCustomInstallForm
 */

namespace Drupal\chm\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements custom config form for MY_PROFILE
 */
class ChmCustomInstallForm extends FormBase {

  public static function getModules() {
    // TODO return by Group CHM.
    return array(
      'chm_comment' => 'Allow people to add comments on content',
      'chm_events' => 'Events (events, calendar widgets, iCal etc.)',
    );
  }

  public function getFormId() {
    return 'chm_custom_install_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $modules = self::getModules();
    $form = array(
      '#tree' => TRUE,
      '#title' => $this->t('Configure CHM toolkit')
    );
    $form['features'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Functionalities')
    );
    $form['features']['modules'] = array(
      '#type' => 'checkboxes',
      '#options' => $modules,
      '#default_value' => array_combine(array_keys($modules), array_keys($modules)),
    );

    $modules = array(
      'google_analytics' => $this->t('Google Analytics'),
      'piwik' => $this->t('Piwik')
    );
    $form['analytics'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Visitor tracking'),
      '#default_value' => array(),
    );
    $form['analytics']['modules'] = array(
      '#type' => 'checkboxes',
      '#options' => $modules,
      '#default_value' => array(),
    );


    $existing_default = \Drupal::state()->get('chm_profile_modules');
    if (!empty($existing_default)) {
      $form['modules']['#default_value'] = $existing_default;
    }
    $form['notice'] = array(
      '#type' => 'item',
      '#markup' => $this->t('Select the items you wish to start with. Those left unchecked can be enabled later on.'),
    );
    $form['op'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $all = array('country_taxonomy');
    $modules = $form_state->getValue('features');
    $all += array_filter($modules['modules']);

    $modules = $form_state->getValue('analytics');
    $all += array_filter($modules['modules']);

    \Drupal::state()->set('chm_profile_modules', $all);
  }
}
