<?php

namespace Drupal\wn_realtime_seo;

use Drupal\Component\Utility\NestedArray;
use Drupal\yoast_seo\YoastSeoFieldManager;

final class RealtimeSeoFieldManager extends YoastSeoFieldManager {

  /**
   * @param array $form_after_build
   *   Node form after build.
   *
   * @return mixed
   *   Transformed form.
   */
  public function setFieldsConfiguration($form_after_build) {
    $yoast_settings = $form_after_build['#yoast_settings'];

    if (FALSE === $this->hasSufficientSettings($yoast_settings)) {
      return $form_after_build;
    }

    $body_field = $yoast_settings['body'] ?? '';
    $summary_field = $yoast_settings['summary'] ?? '';

    if ('' === $body_field) {
      return $form_after_build;
    }

    $body_field_path = $this->getFieldPath($body_field, $form_after_build);
    $body_field_element = $this->getFieldElement($body_field, $form_after_build);

    if (empty($body_field_element)) {
      return $form_after_build;
    }

    $summary_field_path = $this->getFieldPath($summary_field, $form_after_build);

    $this->fieldsConfiguration['paths'][$body_field] = $body_field_path;
    $this->fieldsConfiguration['paths']['summary'] = $summary_field_path;

    $this->fieldsConfiguration['fields'][] = $body_field;

    $this->fieldsConfiguration['tokens']['[node:' . $body_field . ']'] = $body_field;
    $this->fieldsConfiguration['tokens']['[current-page:' . $body_field . ']'] = $body_field;

    $yoastSeoSettingsBuilder = new YoastSeoDrupalSettingsBuilder();

    $fields = [];
    foreach ($this->fieldsConfiguration['fields'] as $field_name) {
      $field_id = (string) $this->formGet($form_after_build, $this->fieldsConfiguration['paths'][$field_name] . '.#id');
      if (str_contains($field_name, '::')) {
        $field_id = str_replace('-wrapper', '-0-value', $field_id);
      }

      if ($field_name == $body_field) {
        $fields['body'] = $field_id;
        continue;
      }

      $fields[$field_name] = $field_id;
    }

    $fields['meta_title'] = $form_after_build['field_meta_tags']['widget'][0]['basic']['title']['#id'];
    $fields['meta_description'] = $form_after_build['field_meta_tags']['widget'][0]['basic']['description']['#id'];

    $yoastSeoSettingsBuilder->setFields($fields);

    $tokens = $this->fieldsConfiguration['tokens'];
    $tokens['[site:name]'] = \Drupal::config('system.site')->get('name');
    $tokens['[site:slogan]'] = \Drupal::config('system.site')->get('slogan');
    $yoastSeoSettingsBuilder->setTokens($tokens);

    $is_default_meta_title = !empty($form_after_build['field_meta_tags']['widget'][0]['basic']['title']['#default_value']) ? TRUE : FALSE;
    $is_default_keyword = !empty($form_after_build['field_yoast_seo']['widget'][0]['yoast_seo']['focus_keyword']['#default_value']) ? TRUE : FALSE;
    $is_default_meta_description = !empty($form_after_build['field_meta_tags']['widget'][0]['basic']['description']['#default_value']) ? TRUE : FALSE;
    $body_exists = !empty($body_field_element['#default_value']) ? TRUE : FALSE;

    $default_text = [
      'meta_title' => $is_default_meta_title ? $form_after_build['field_meta_tags']['widget'][0]['basic']['title']['#default_value'] : '',
      'keyword' => $is_default_keyword ? $form_after_build['field_yoast_seo']['widget'][0]['yoast_seo']['focus_keyword']['#default_value'] : '',
      'meta_description' => $is_default_meta_description ? $form_after_build['field_meta_tags']['widget'][0]['basic']['description']['#default_value'] : '',
      $body_field => $body_exists ? $body_field_element['#default_value'] : '',
      'path' => $form_after_build['path']['widget'][0]['source']['#value'] ?? '',
    ];
    $yoastSeoSettingsBuilder->setDefaultText($default_text);

    $placeholders = [
      'snippetTitle' => t('Please click here to alter your page meta title'),
      'snippetMeta' => t('Please click here and alter your page meta description.'),
      'snippetCite' => t('/example-post'),
    ];
    $yoastSeoSettingsBuilder->setPlaceholders($placeholders);

    $yoastSeoSettingsBuilder->setSeoTitleOverwritten($is_default_meta_title);
    $yoastSeoSettingsBuilder->setTextFormat($body_field_element['#format'] ?? '');
    $yoastSeoSettingsBuilder->setFormId($form_after_build['#id']);

    $form_after_build['#attached']['drupalSettings'] = array_replace_recursive(
      $form_after_build['#attached']['drupalSettings'] ?? [],
      $yoastSeoSettingsBuilder->getSettings()
    );

    return $form_after_build;
  }

  private function formGet(array $form, string $key): mixed {
    return NestedArray::getValue(
      $form,
      explode('.', $key)
    );
  }

  /**
   * @param array<string,string> $yoast_settings
   */
  private function hasSufficientSettings(array $yoast_settings): bool {
    return (isset($yoast_settings['body']) && isset($yoast_settings['summary']));
  }

  private function getFieldPath(string $field_name, array $form_after_build): string {
    $field_explode = explode('::', $field_name);

    if (isset($field_explode[1])) {
      $entity_relation_item = $field_explode[0];
      $field = $field_explode[1];

      if (isset($form_after_build[$entity_relation_item]['widget'][0]['subform'][$field])) {
        return sprintf('%s.widget.0.subform.%s', $entity_relation_item, $field);
      }

      return '';
    }

    $field = $field_explode[0];
    return sprintf('%s.widget.0.value', $field);
  }

  private function getFieldElement(string $field_name, array $form_after_build): array {
    $field_explode = explode('::', $field_name);

    if (isset($field_explode[1])) {
      $entity_relation_item = $field_explode[0];
      $field = $field_explode[1];

      return $form_after_build[$entity_relation_item]['widget'][0]['subform'][$field] ?? [];
    }

    $field = $field_explode[0];
    return $form_after_build[$field]['widget'][0] ?? [];
  }

}
