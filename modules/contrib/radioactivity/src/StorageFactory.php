<?php

namespace Drupal\radioactivity;

/**
 * Storage factory service.
 */
class StorageFactory {

  /**
   * Getter for classes which implement IncidentStorageInterface.
   *
   * @param string $type
   *   The type of storage to get.
   *
   * @return \Drupal\Radioactivity\IncidentStorageInterface
   *   Instance of the requested storage.
   */
  static public function get($type) {

    static $instances = [];
    if (isset($instances[$type])) {
      return $instances[$type];
    }

    switch ($type) {
      case 'rest_local':
        $instance = new RestIncidentStorage();
        break;

      case 'rest_remote':
        $instance = new RestIncidentStorage(\Drupal::config('radioactivity.storage')->get('endpoint'));
        break;

      case 'default':
      default:
        $instance = new DefaultIncidentStorage();
    }

    $instances[$type] = $instance;

    return $instance;
  }

  /**
   * Get the configured incident storage.
   *
   * @return \Drupal\Radioactivity\IncidentStorageInterface
   *   The configured storage instance.
   */
  public function getConfiguredStorage() {
    $factory = \Drupal::service('radioactivity.storage');
    $type = \Drupal::config('radioactivity.storage')->get('type');
    if (!$type) {
      $type = 'default';
    }
    return $factory->get($type);
  }

}
