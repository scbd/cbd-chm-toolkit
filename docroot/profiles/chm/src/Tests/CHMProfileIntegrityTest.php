<?php

namespace Drupal\chm\Tests;

/**
 * Tests the chm installation profile.
 *
 * @group chm
 */
class CHMProfileIntegrityTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'chm';
  protected $strictConfigSchema = FALSE;

  function testTheme() {
    $this->drupalGet('<front>');
    // Check the bootstrap theme has been loaded.
    $this->assertRaw('bootstrap.css');
  }

  function testTaxonomyTags() {
    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    $this->assertTrue(array_key_exists('tags', $vocabularies), 'Taxonomy "tags" exists');
  }
}
