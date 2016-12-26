<?php

namespace Drupal\radioactivity\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'radioactivity' field type.
 *
 * @FieldType(
 *   id = "radioactivity",
 *   label = @Translation("Radioactivity"),
 *   description = @Translation("Radioactivity energy level and energy emitter"),
 *   default_widget = "radioactivity_energy",
 *   default_formatter = "radioactivity_emitter"
 * )
 */
class RadioactivityField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'profile' => 0,
      'halflife' => 60 * 60 * 12,
      'granularity' => 60 * 15,
      'cutoff' => 10,
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['energy'] = DataDefinition::create('float')
      ->setLabel(new TranslatableMarkup('Energy level'));

    $properties['timestamp'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Energy timestamp'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'energy' => array(
          'description' => 'Energy level',
          'type' => 'float',
          'default' => 0,
        ),
        'timestamp' => array(
          'description' => 'Timestamp of last emit',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      // @todo What to do with this?
      //$constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
      /*$constraints[] = $constraint_manager->create('ComplexData', array(
      'value' => array(
      'Length' => array(
      'max' => $max_length,
      'maxMessage' => t('%name: may not be longer than @max characters.', array(
      '%name' => $this->getFieldDefinition()->getLabel(),
      '@max' => $max_length
      )),
      ),
      ),
      ));*/
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['energy'] = 1;
    $values['timestamp'] = REQUEST_TIME;
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    $elements['profile'] = array(
      '#type' => 'radios',
      '#title' => t('Energy profile'),
      '#default_value' => $this->getSetting('profile'),
      '#required' => TRUE,
      '#options' => array(
        'count' => 'Count',
        'linear' => 'Linear',
        'decay' => 'Decay',
      ),
      '#description' => t('Count: Energy increases by 1 with each view. Never decreases.<br/>
Linear: Energy increases by the emission amount. Decreases by 1 per second.<br/>
Decay: Energy increases by the emission amount. Decreases 50% per half-life time.'),
    );

    $elements['granularity'] = array(
      '#type' => 'number',
      '#title' => t('Granularity'),
      '#min' => 1,
      '#default_value' => $this->getSetting('granularity'),
      '#description' => t('The time in seconds that the energy levels are kept before applying the decay.'),
      '#states' => array(
        'visible' => array(
          'input[name="settings[profile]"]' => array('value' => 'linear'),
        ),
      ),
    );

    $elements['halflife'] = array(
      '#type' => 'number',
      '#title' => t('Half-life time'),
      '#min' => 1,
      '#default_value' => $this->getSetting('halflife'),
      '#description' => t('The time in seconds in which the energy level halves.'),
      '#states' => array(
        'visible' => array(
          'input[name="settings[profile]"]' => array('value' => 'decay'),
        ),
      ),
    );

    $elements['cutoff'] = array(
      '#type' => 'textfield',
      '#title' => t('Cutoff'),
      '#pattern' => '[0-9]+(\.[0-9]+)?',
      '#default_value' => $this->getSetting('cutoff'),
      '#description' => t('Energy levels under this value is set to zero. Example: 0.5, 2.'),
      '#states' => array(
        'invisible' => array(
          'input[name="settings[profile]"]' => array('value' => 'count'),
        ),
      ),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    if (!$this->energy) {
      $this->energy = 0;
    }
    $this->timestamp = REQUEST_TIME;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('energy')->getValue();
    return $value === NULL;
  }

}
