<?php

namespace Drupal\radioactivity\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Web tests for Radioactivity.
 *
 * @group radioactivity
 */
class RadioactivityWebTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('radioactivity', 'node');

  /**
   * Perform any initial set up tasks that run before every test method.
   */
  public function setUp() {
    parent::setUp();
  }


  /**
   * Tests that the REST does not accept invalid requests.
   */
  public function testRestValidation() {

    $this->drupalGet('radioactivity/emit');
    $this->assertResponse(200);

  }


}
