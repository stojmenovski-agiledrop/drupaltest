<?php

declare(strict_types = 1);

namespace Drupal\jobiqo_synonyms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin for Synonyms widget.
 *
 * @FieldWidget(
 *   id = "jobiqo_synonyms_widget",
 *   label = @Translation("Synonyms widget"),
 *   field_types = {
 *     "jobiqo_synonyms"
 *   }
 * )
 */
class SynonymsWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   *
   * Turn multiple text field widgets into single textarea. That's why we need
   * only first element.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    // Keep the only first element.
    if ($delta > 0) {
      return [];
    }
    // Get synonyms.
    $value = [];
    foreach ($items as $item) {
      $value[] = $item->synonym;
    }
    $widget = [
      '#type' => 'textarea',
      '#title' => $this->t('Synonyms'),
      '#default_value' => implode("\n", $value),
      '#description' => $this->t('Enter one synonym per line.'),
    ];
    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state): array {
    $widget = parent::formMultipleElements($items, $form, $form_state);
    // Keep only first textarea element as turned multiple text field widgets
    // into single textarea widget.
    foreach ($items as $delta => $item) {
      if ($delta > 0) {
        unset($widget[$delta]);
      }
    }
    // Turn to single element.
    $widget['#cardinality_multiple'] = FALSE;
    // This is important so the form builder does not try to loop over elements.
    unset($widget[0]['_weight']);
    // No need for add more button.
    unset($widget['add_more']);
    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state): void {
    $field_name = $this->fieldDefinition->getName();
    $value = $form_state->getValue($field_name);
    // The value is stored under the first element. If there are more than one
    // elements the values were already processed and extracted.
    if (count($value) == 1) {
      $textarea_input = $value[0] ?? '';
      $value = [];
      foreach (preg_split("/(\r\n|\n|\r)/", $textarea_input) as $synonym) {
        if ($synonym = trim($synonym)) {
          $value[] = $synonym;
        }
      }
    }
    $items->setValue($value);
  }

}
