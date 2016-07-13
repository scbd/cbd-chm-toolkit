<?php

/**
 * Implements hook_form_FORM_ID_alter().
 */
function ptk_base_form_domain_form_alter(&$form, &$form_state, $form_id) {
  ChmDomainForm::alter($form, $form_state);
}

class ChmDomainForm {

  static function alter(&$form, &$form_state) {
    $form['is_default']['#access'] = FALSE;
    $is_new = empty($form['#domain']['domain_id']);
    $domain = $form['#domain'];
    if (!PTKDomain::isDefaultDomain($domain)) {
      $countries = PTK::getCountryListAsOptions();
      $form['country'] = [
        '#title' => t('Country'),
        '#description' => t('Designate the country for this CHM portal'),
        '#type' => 'select',
        '#options' => $countries,
        '#empty_option' => t('- Select -'),
        '#default_value' => PTKDomain::variable_get('country', $form['#domain']),
        '#required' => FALSE,
        '#weight' => -10,
      ];
    }
    module_load_include('inc', 'domain_variable_locale', 'domain_variable_locale.variable');
    $languages = PTKDomain::variable_get('language_list', $form['#domain']);
    $languages = !empty($languages) ? array_values($languages) : array();
    if ($is_new && empty($languages)) {
      $languages = array('en');
    }
    if ($is_new) {
      $form['languages'] = [
        '#type' => 'fieldset',
        '#title' => t('List of active languages'),
        '#weight' => 40,
      ];
      $form['languages']['language_list'] = [
        '#title' => t('Select the languages to be available on this website'),
        '#type' => 'checkboxes',
        '#options' => domain_variable_locale_language_list([], []),
        '#default_value' => $languages,
        '#required' => TRUE,
      ];
      $form['ptk'] = [
        '#type' => 'fieldset',
        '#title' => t('Initialize domain content'),
        '#weight' => 50,
      ];
      $form['ptk']['create_main_menu'] = [
        '#type' => 'checkbox',
        '#title' => t('Create main menu & static pages'),
      ];
      $form['ptk']['create_sample_content'] = [
        '#type' => 'checkbox',
        '#title' => t('Create sample content (i.e. project, species etc.)'),
      ];
      $social = ChmSocialMediaForm::form($form);
      $form['ptk']['social'] = $social['ptk']['social'];

      $form['weight']['#access'] = FALSE;
      $form['submit']['#weight'] = 100;
      $form['submit']['#value'] = t('Create domain record');
    }
    $form['#is_new'] = $is_new;
    $form['#submit'][] = array('ChmDomainForm', 'submit');
    $form['#validate'][] = array('ChmDomainForm', 'validate');
  }

  static function validate($form, $form_state) {
    $iso = $form_state['values']['country'];
    if ($iso && $country = PTK::getCountryByCode($form_state['values']['country'])) {
      // Do not allow to set a country without having Protected Planet ID set
      /** @var stdClass $wrapper */
      $wrapper = entity_metadata_wrapper('taxonomy_term', $country);
      $ppid = $wrapper->field_protected_planet_id->value();
      if (empty($ppid)) {
        form_set_error(
          'country',
          t('Cannot use this country because is missing the <b>Protected Planet</b> ID, configure it !here',
            array('!here' => l(t('here'), 'taxonomy/term/' . $country->tid . '/edit', array('attributes' => array('target' => '_blank')))))
        );
      }
    }
  }

  static function submit($form, $form_state) {
    $current = PTKDomain::variable_get('country', $form['#domain']);
    $domain = $form['#domain'];
    if (empty($domain['machine_name'])) {
      $domain['machine_name'] = $form_state['values']['machine_name'];
    }
    $countryIsoCode = $form_state['values']['country'];
    if ($current !== $countryIsoCode) {
      PTKDomain::variable_set('country', $countryIsoCode, $domain);
    }
    cache_clear_all();
    if (!empty($form['#is_new'])) {
      $machine_name = $form_state['values']['machine_name'];
      $domain_id = domain_load_domain_id($machine_name);
      $domain = domain_load($domain_id);
      PTKDomain::initializeCountryDomain($domain, $form_state['values']);
      ChmSocialMediaForm::submit($form, $form_state, $domain);
      cache_clear_all();
    }

    // Add the species import batch
    $batch = array(
      'title' => t('Invoking IUCN RedList API species data, please be patient ...'),
      'operations' => array(
        array(
          array('ChmDomainForm', 'importSpeciesForCountry'),
          array($countryIsoCode)
        ),
      ),
      'finished' => array('ChmDomainForm', 'importSpeciesForCountryFinished')
    );
    batch_set($batch);
  }


  public static function importSpeciesForCountry($iso, &$context) {
    $iucn = IUCNRedListAPI::instance();
    $count = $iucn->populateCountrySpecies($iso);
    $context['results'] = $count;
  }

  public static function importSpeciesForCountryFinished($success, $results) {
    if ($success) {
      $message = "$results species processed from IUCN RedList API.";
    }
    else {
      $message = t('Species synchronisation finished with an error.');
    }
    drupal_set_message($message);
  }


}