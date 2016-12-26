<?php

namespace Drupal\radioactivity;

use Drupal\Component\Serialization\Json;

/**
 * Defines a REST incident storage.
 */
class RestIncidentStorage implements IncidentStorageInterface {

  /**
   * Constructor.
   */
  public function __construct($endpoint = FALSE) {
    if ($endpoint) {
      $this->endpoint = $endpoint;
    }
    else {
      $this->endpoint = $base_url . '/' . drupal_get_path('module', 'radioactivity') . '/endpoints/file/rest.php';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addIncident(Incident $incident) {
    throw new \Exception("Rest endpoint expects the URL to be configured somewhere else.");
  }

  /**
   * {@inheritdoc}
   */
  public function getIncidents() {
    $data = Json::decode(file_get_contents($this->endpoint . '?get'));
    $result = [];
    foreach ($data as $rows) {
      foreach ($rows as $incident) {
        $incident = Incident::createFromPostData($incident);
        if ($incident->isValid()) {
          $result[] = $incident;
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getIncidentsByType($entity_type = '') {
    $incidents = array();

    /** @var \Drupal\radioactivity\Incident[] $stored_incidents */
    $stored_incidents = $this->getIncidents();
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
    file_get_contents($this->endpoint . '?clear');
  }

  /**
   * {@inheritdoc}
   */
  public function injectSettings(&$page) {
    $page['#attached']['drupalSettings']['type'] = 'rest';
    $page['#attached']['drupalSettings']['radioactivity']['endpoint'] = $this->endpoint;
  }

}
