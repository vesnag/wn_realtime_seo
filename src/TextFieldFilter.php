<?php

namespace Drupal\wn_realtime_seo;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

final class TextFieldFilter {

  use StringTranslationTrait;

  public function __construct(
    private EntityFieldManagerInterface $entityFieldManager
  ) {}

  public function filterTextFields(array $field_definitions, string $parent_entity = ''): array {
    $text_fields = [];

    foreach ($field_definitions as $field_name => $field_definition) {
      $field_key = $parent_entity ? sprintf('%s::%s', $parent_entity, $field_name) : $field_name;

      if ($this->isTextField($field_definition)) {
        $text_fields[$field_key] = $parent_entity ? sprintf('%s: %s', $parent_entity, $field_definition->getLabel()) : $field_name;
        continue;
      }

      if ($this->isEntityReferenceToParagraph($field_definition)) {
        $paragraph_field_definitions = $this->getParagraphFieldDefinitions($field_definition);
        $nested_text_fields = $this->filterTextFields($paragraph_field_definitions, $field_key);
        $text_fields = array_merge($text_fields, $nested_text_fields);
      }
    }

    return $text_fields;
  }

  private function isTextField(FieldDefinitionInterface $field_definition): bool {
    return in_array($field_definition->getType(), $this->textFieldTypes());
  }

  private function isEntityReferenceToParagraph(FieldDefinitionInterface $field_definition): bool {
    return in_array($field_definition->getType(), ['entity_reference', 'entity_reference_revisions']) &&
      $field_definition->getSetting('handler') === 'default:paragraph';
  }

  private function getParagraphFieldDefinitions(FieldDefinitionInterface $field_definition): array {
    $target_bundles = $field_definition->getSetting('handler_settings')['target_bundles'];
    // Assuming single target bundle, adjust logic if multiple bundles are needed.
    $bundle = (string) reset($target_bundles);
    return $this->entityFieldManager->getFieldDefinitions('paragraph', $bundle);
  }

  private function textFieldTypes(): array {
    return [
      'text_with_summary',
      'text_long',
      'string_long',
      'string',
    ];
  }

}
