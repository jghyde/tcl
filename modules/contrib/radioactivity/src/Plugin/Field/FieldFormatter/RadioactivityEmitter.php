<?php

namespace Drupal\radioactivity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\radioactivity\Incident;

/**
 * Plugin implementation of the 'radioactivity_emitter' formatter.
 *
 * @FieldFormatter(
 *   id = "radioactivity_emitter",
 *   label = @Translation("Emitter"),
 *   field_types = {
 *     "radioactivity"
 *   }
 * )
 */
class RadioactivityEmitter extends FormatterBase {

  /**
   * The emission counter.
   *
   * @var int
   */
  protected static $emitCount;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'energy' => 10,
      'display' => 'none',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array(
      // Implement settings form.
      'energy' => array(
        '#title' => 'Energy',
        '#type' => 'textfield',
        '#description' => 'The amount of energy to emit when this field is displayed.',
        '#pattern' => '[0-9\.]*',
        '#default_value' => $this->getSetting('energy'),
      ),
      'display' => array(
        '#title' => 'Display',
        '#type' => 'select',
        '#options' => [
          'none' => 'No value - only emit',
          'raw' => 'Energy level',
        ],
        '#default_value' => $this->getSetting('display'),
      ),
    ) + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Emit: @energy', array('@energy' => $this->getSetting('energy')));
    switch ($this->getSetting('display')) {
      case 'none':
        $summary[] = 'Only emit';
        break;

      case 'raw':
        $summary[] = 'Display energy level';
        break;
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {

      $incident = Incident::createFromFieldItemsAndFormatter($items, $item, $this);

      if (!self::$emitCount) {
        self::$emitCount = 0;
      }

      $key = 'ra_emit_' . self::$emitCount++;

      $elements[$delta] = [
        '#attached' => [
          'library' => ['radioactivity/triggers'],
          'drupalSettings' => [
            $key => $incident->toJson(),
          ],
        ],
      ];

      switch ($this->getSetting('display')) {
        case 'raw':
          $elements[$delta]['#markup'] = $this->viewValue($item);
          break;
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    if ($this->getSetting('display') != 'none') {
      return parent::view($items, $langcode);
    }

    return array();
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    return $item->energy;
  }

}
