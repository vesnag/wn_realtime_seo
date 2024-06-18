<?php

namespace Drupal\wn_realtime_seo;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

final class TextFieldFilter {

  use StringTranslationTrait;

  public function __construct(
    private EntityFieldManagerInterface $entityFieldManager
  ) {}

  /**
   * Filters text fields from the given field definitions.
   *
   * @param array $field_definitions
   *   The list of field definitions.
   *
   * @return array
   *   An associative array of field names and their labels.
   */
  public function filterTextFields(array $field_definitions, string $parent_entity = '') {
    $text_fields = [];

    foreach ($field_definitions as $field_name => $field_definition) {
      // TODO sprintf.
      $field_key = $parent_entity ? "$parent_entity::$field_name" : $field_name;

      if ($this->isTextField($field_definition)) {
        // TODO sprintf.
        $text_fields[$field_key] = $parent_entity ? "$parent_entity: " . $field_definition->getLabel() : $field_name;
      }
      elseif ($this->isEntityReferenceToParagraph($field_definition)) {
        $paragraph_field_definitions = $this->getParagraphFieldDefinitions($field_definition);
        $nested_text_fields = $this->filterTextFields($paragraph_field_definitions, $field_key);
        $text_fields = array_merge($text_fields, $nested_text_fields);
      }
    }

    return $text_fields;
  }

  /**
   * Checks if a field definition is a text field.
   *
   * @param array $field_definition
   *   The field definition.
   *
   * @return bool
   *   TRUE if the field is a text field, FALSE otherwise.
   */
  private function isTextField(array $field_definition): bool {
    return in_array($field_definition->getType(), $this->textFieldTypes());
  }

  /**
   * Checks if a field definition is an entity reference to a paragraph.
   *
   * @param array $field_definition
   *   The field definition.
   *
   * @return bool
   *   TRUE if the field is an entity reference to a paragraph, FALSE otherwise.
   */
  private function isEntityReferenceToParagraph(array $field_definition): bool {
    return in_array($field_definition->getType(), ['entity_reference', 'entity_reference_revisions']) &&
      $field_definition->getSetting('handler') === 'default:paragraph';
  }

  /**
   * Retrieves field definitions for the referenced paragraph.
   *
   * @param array $field_definition
   *   The field definition.
   *
   * @return array
   *   The field definitions for the referenced paragraph.
   */
  private function getParagraphFieldDefinitions(array $field_definition): array {
    $target_bundles = $field_definition->getSetting('handler_settings')['target_bundles'];
    // Assuming single target bundle, adjust logic if multiple bundles are needed.
    $bundle = reset($target_bundles);
    return $this->entityFieldManager->getFieldDefinitions('paragraph', $bundle);
  }

  /**
   * Returns the list of text field types.
   *
   * @return array
   *   An array of text field types.
   */
  private function textFieldTypes(): array {
    return [
      'text_with_summary',
      'text_long',
      'string_long',
    ];
  }

}
