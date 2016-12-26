<?php

namespace Drupal\radioactivity;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\State\State;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class RadioactivityProcessor.
 *
 * @package Drupal\radioactivity
 */
class RadioactivityProcessor implements RadioactivityProcessorInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * State key-value storage.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $log;

  /**
   * Radioactivity storage.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $storage;

  /**
   * Constructs a Radioactivity processor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\State\State $state
   *   The key-value storage.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   The logger.
   */
  public function __construct(EntityTypeManager $entity_type_manager, State $state, QueryFactory $entity_query, LoggerChannelFactory $logger_factory, StorageFactory $storage) {
    $this->entityTypeManager = $entity_type_manager;
    $this->state = $state;
    $this->entityQuery = $entity_query;
    $this->log = $logger_factory->get('radioactivity');
    $this->storage = $storage->getConfiguredStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function processDecay() {
    $result_count = 0;

    /** @var \Drupal\field\Entity\FieldStorageConfig[] $field_storage_configs */
    $field_storage_configs = $this->entityTypeManager->getStorage('field_storage_config')->loadByProperties(array('type' => 'radioactivity'));

    if (!$field_storage_configs) {
      return;
    }

    $last_cron_timestamp = $this->state->get('radioactivity_last_cron_timestamp', 0);

    $this->state->set('radioactivity_last_cron_timestamp', REQUEST_TIME);

    foreach ($field_storage_configs as $field_storage) {

      // Only act when field has data.
      if ($field_storage->hasData()) {
        $this->updateField($field_storage, $last_cron_timestamp);
      }
    }

    $this->log->notice('Processed @count entities.', array('@count' => $result_count));

  }

  /**
   * Update entities attached to given field storage.
   * @param  [type] $last_cron_timestamp [description]
   */
  private function updateField($field_storage, $last_cron_timestamp) {

    $profile = $field_storage->getSetting('profile');

    // For count profile, we don't need to calculate a decay.
    if ($profile == 'count') {
      return;
    }

    $halflife = $field_storage->getSetting('halflife');
    $granularity = $field_storage->getSetting('granularity');
    $cutoff = $field_storage->getSetting('cutoff');

    // For decay profile, we only calculate the decay when 'granularity'
    // seconds have passed since the last cron run.
    if ($profile == 'decay') {
      $threshold_timestamp = $last_cron_timestamp - ($last_cron_timestamp % $granularity) + $granularity;
      if (REQUEST_TIME < $threshold_timestamp) {
        // Granularity not reached yet.
        return;
      }
    }

    $field_name = $field_storage->get('field_name');
    $entity_type = $field_storage->getTargetEntityTypeId();

    // @todo The number of nodes may grow very large on active sites and/or
    //   long cron cycle times. Prepare for queue processing.
    $query = $this->entityQuery->get($entity_type)
      // @todo Why use timestamp in this query? See https://www.drupal.org/node/2677688
      //->condition($field_name . '.timestamp', REQUEST_TIME, ' < ')
      ->condition($field_name . '.energy', NULL, 'IS NOT NULL');
    $nids = $query->execute();
    /** @var \Drupal\Core\Entity\ContentEntityInterface[] $entities */
    $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($nids);

    // Loop through entities:
    foreach ($entities as $entity) {

      $timestamp = $entity->{$field_name}->timestamp;
      $elapsed = $timestamp ? REQUEST_TIME - $timestamp : 0;
      $energy = $entity->{$field_name}->energy;

      switch ($profile) {
        case 'linear':
          $energy = $energy > $elapsed ? $energy - $elapsed : 0;
          break;

        case 'decay':
          $energy = $energy * pow(2, -$elapsed / $halflife);
          break;
      }

      if ($energy > $cutoff) {
        // Set the new energy level and update the timestamp.
        $entity->{$field_name}->setValue([
          'energy' => $energy,
          'timestamp' => REQUEST_TIME,
        ]);
      }
      else {
        // Reset energy level to 0 if they are below the cutoff value.
        $entity->{$field_name}->setValue(NULL);
      }

      $entity->save();
      $result_count++;

    }

  }

  /**
   * {@inheritdoc}
   */
  public function processEmits() {
    $count = 0;

    // @todo The number of incidents may grow very large on active sites and/or
    // long cron cycle times. Prepare for queue processing.

    // Get incident data.
    $incidents_by_type = $this->storage->getIncidentsByType();
    $this->storage->clearIncidents();

    foreach ($incidents_by_type as $type => $incidents) {
      $entities = $this->entityTypeManager->getStorage($type)->loadMultiple(array_keys($incidents));
      foreach ($entities as $entity) {
        /** @var \Drupal\radioactivity\Incident $incident */
        foreach ($incidents[$entity->id()] as $incident) {
          $entity->{$incident->getFieldName()}->energy += $incident->getEnergy();
        }
        $entity->save();
        $count++;
      }
    }

    $this->log->notice('Processed @count incidents.', array('@count' => $count));

    return $count;
  }

}
