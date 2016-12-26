<?php

namespace Drupal\radioactivity;

use \Drupal\radioactivity\Incident;

/**
 * Defines a default incident storage.
 */
class DefaultIncidentStorage implements IncidentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function addIncident(Incident $incident) {
    $incidents = \Drupal::state()->get('radioactivity_incidents', []);
    $incidents[] = $incident;
    \Drupal::state()->set('radioactivity_incidents', $incidents);
  }

  /**
   * {@inheritdoc}
   */
  public function getIncidents() {
    return \Drupal::state()->get('radioactivity_incidents', []);
  }

  /**
   * {@inheritdoc}
   */
  public function getIncidentsByType($entity_type = '') {
    $incidents = array();

    /** @var \Drupal\radioactivity\Incident[] $stored_incidents */
    $stored_incidents = \Drupal::state()->get('radioactivity_incidents', []);
    foreach ($stored_incidents as $incident) {
      $incidents[$incident->getEntityTypeId()][$incident->getEntityId()][] = $incident;
    }

    if ($entity_type) {
      return isset($incidents[$entity_type]) ? $incidents[$entity_type] : array();
    }
    return $incidents;
  }

  /**
   * {@inheritdoc}
   */
  public function clearIncidents() {
    \Drupal::state()->set('radioactivity_incidents', []);
  }

  /**
   * {@inheritdoc}
   */
  public function injectSettings(&$page) {
    global $base_url;
    $page['#attached']['drupalSettings']['type'] = 'default';
    $page['#attached']['drupalSettings']['radioactivity']['endpoint'] = $base_url . "/radioactivity/emit";
  }

}
