<?php

namespace Drupal\radioactivity\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'radioactivity_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "radioactivity_energy",
 *   label = @Translation("Energy"),
 *   field_types = {
 *     "radioactivity"
 *   }
 * )
 */
class RadioactivityEnergy extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'energy' => 0.0,
      'timestamp' => REQUEST_TIME,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['energy'] = $element + array(
      '#type' => 'textfield',
      '#pattern' => '[0-9]+(\.[0-9]+)?',
      '#default_value' => isset($items[$delta]->energy) ? $items[$delta]->energy : 0,
       // '#description' => $this->t('Example: 5.5, 10.'),
    );

    // @todo Should timestamp be configurable? See https://www.drupal.org/node/2677688
    // $element['timestamp'] = array(
    //   '#type' => 'hidden',
    //   '#default_value' => REQUEST_TIME;
    // );

    return $element;
  }

}
