<?php

namespace Drupal\ptk_base\blocks;

abstract class AbstractBlock {

  protected $delta;

  public function __construct() {
    $this->delta = $this->getDelta();
  }

  /**
   * Returns the machine name of the block.
   *
   * @return string
   */
  abstract public function getDelta();

  /**
   * Implements hook_block_view().
   */
  abstract public function view();

  /**
   * An array containing the settings of the block.
   * The return array should be based on Drupal's form api.
   * Each item will be created within block's configure form and will be saved
   * in the database as a variable. (Variable names will have the following
   * format: $block_delta . '_' . $setting_name)
   *
   * @return array
   */
  protected function settings() {
    return [];
  }

  /**
   * Implements hook_block_configure().
   */
  public function configure() {
    $form = [];
    foreach ($this->settings() as $name => $setting) {
      $name = $this->delta . '_' . $name;
      if (is_array($setting)) {
        $form[$name] = $setting;
        $form[$name]['#default_value'] = variable_get($name);
      }
      else {
        watchdog('ptk_base_blocks', 'Invalid settings array', [], WATCHDOG_WARNING);
      }
    }
    return $form;
  }

  /**
   * Implements hook_block_save().
   */
  public function save($edit = array()) {
    foreach ($this->settings() as $name => $setting) {
      $name = $this->delta . '_' . $name;
      variable_set($name, $edit[$name]);
    }
  }
}
