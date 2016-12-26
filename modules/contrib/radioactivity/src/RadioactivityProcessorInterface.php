<?php

namespace Drupal\radioactivity;

/**
 * Interface RadioactivityProcessorInterface.
 *
 * @package Drupal\radioactivity
 */
interface RadioactivityProcessorInterface {

  /**
   * Apply decay to entities.
   */
  public function processDecay();

  /**
   * Process emits from the queue.
   */
  public function processEmits();

}
