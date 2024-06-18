<?php

namespace Drupal\wn_realtime_seo;

use Drupal\Component\Utility\NestedArray;

final class RealtimeSeoFieldManager {

  public $fieldsConfiguration = [
    // Paths to access the fields inside the form array.
    'paths' => [
      'title' => 'title.widget.0.value',
      'focus_keyword' => 'field_yoast_seo.widget.0.yoast_seo.focus_keyword',
      'seo_status' => 'field_yoast_seo.widget.0.yoast_seo.status',
      'path' => 'path.widget.0.alias',
    ],

    // Fields to include in the field section of the configuration.
    'fields' => [
      'title',
      'summary',
      'focus_keyword',
      'seo_status',
      'path',
    ],

    // Tokens for the fields.
    'tokens' => [
      '[current-page:title]' => 'title',
      '[node:title]' => 'title',
      '[current-page:summary]' => 'summary',
      '[node:summary]' => 'summary',
    ],
  ];

  /**
   * Set fields configuration from a form.
   *
   * Explores the field present in the form and build a setting array
   * that will be used by yoast_seo javascript.
   *
   * @param array $form_after_build
   *   Node form after build.
   *
   * @return mixed
   *   Transformed form.
   */
  public function setFieldsConfiguration($form_after_build) {
    $yoast_settings = $form_after_build['#yoast_settings'];

    if (!isset($yoast_settings['body']) || !isset($yoast_settings['summary'])) {
      return $form_after_build;
    }

    $body_field = $yoast_settings['body'] ?? '';
    $summary_field = $yoast_settings['summary'] ?? '';

    if ('' === $body_field) {
      return $form_after_build;
    }

    $body_field_explode = explode('::', $body_field);

    $body_element = FALSE;

    $body_field_path = '';
    // TODO move to private method.
    $body_field_name = $body_field_explode[0];
    if ($body_field_name && isset($form_after_build[$body_field]['widget'][0])) {
      $body_element = $form_after_build[$body_field]['widget'][0];
      $body_field_path = $body_field_name.'widget.0.value';
    }

    if (isset($body_field_explode[1])) {
      $entity_relation_item = $body_field_explode[0];
      $field = $body_field_explode[1];

      // TODO check if is nested.
      if (isset($form_after_build[$entity_relation_item]['widget'][0]['subform'][$field])) {
        $body_element = $form_after_build[$entity_relation_item]['widget'][0]['subform'][$field];
      }
      $body_field_path = $entity_relation_item.'.widget.0.subform.'.$field;
    }

    if (FALSE === $body_element) {
      return $form_after_build;
    }

    // TODO get info for the summary.


    // TODO set paths for body and summary
    $this->fieldsConfiguration['paths'][$body_field_name] = '';
    $this->fieldsConfiguration['paths'][$summary_field] = '';

    $summary_path = FALSE;
    $summary_field_explode = explode('::', $summary_field);
    if ('body' === $summary_field) {
      $summary_field_path = $summary_field .'.widget.0.summary';
    }
    else if (isset($summary_field_explode[1])) {
      $summary_field_path = $summary_field_explode[0] .'.widget.0.subform.'.$summary_field_explode[0].'widget.0.value';
      // TODO consider if the referenced paragraph has a body field.
    }
    else {
      $summary_field_path = $summary_field .'.widget.0.value';
    }


    $summary_field = $summary_field_explode[0];
    $summary_element = $form_after_build[$summary_field]['widget'][0];

    if (isset($summary_field_explode[1])) {
      $entity_relation_item = $summary_field_explode[0];
      $field = $summary_field_explode[1];

      // TODO check if is nested.
      if (isset($form_after_build[$entity_relation_item]['widget'][0]['subform'][$field])) {
        $summary_element = $form_after_build[$entity_relation_item]['widget'][0]['subform'][$field];
      }
    }

    $this->fieldsConfiguration['paths'][$body_field] = $body_field_path;
    $this->fieldsConfiguration['paths']['summary'] = $summary_field_path;


    $this->fieldsConfiguration['fields'][] = $body_field;

    $this->fieldsConfiguration['tokens']['[node:' . $body_field . ']'] = $body_field;
    $this->fieldsConfiguration['tokens']['[current-page:' . $body_field . ']'] = $body_field;

    $yoastSeoSettingsBuilder = new YoastSeoDrupalSettingsBuilder();

    $fields = [];
    foreach ($this->fieldsConfiguration['fields'] as $field_name) {
      $field_id = $this->formGet($form_after_build, $this->fieldsConfiguration['paths'][$field_name] . '.#id');
      if (str_contains($field_name, '::')) {
        $field_id = str_replace('-wrapper', '-0-value', $field_id);
      }

      if ($field_name == $body_field) {
        $fields['body'] = $field_id;
      }
      else {
        $fields[$field_name] = $field_id;
      }
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
    $body_exists = !empty($body_element['#default_value']) ? TRUE : FALSE;

    $default_text = [
      'meta_title' => $is_default_meta_title ? $form_after_build['field_meta_tags']['widget'][0]['basic']['title']['#default_value'] : '',
      'keyword' => $is_default_keyword ? $form_after_build['field_yoast_seo']['widget'][0]['yoast_seo']['focus_keyword']['#default_value'] : '',
      'meta_description' => $is_default_meta_description ? $form_after_build['field_meta_tags']['widget'][0]['basic']['description']['#default_value'] : '',
      $body_field => $body_exists ? $body_element['#default_value'] : '',
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
    $yoastSeoSettingsBuilder->setTextFormat(isset($body_element['#format']) ? $body_element['#format'] : '');
    $yoastSeoSettingsBuilder->setFormId($form_after_build['#id']);

    $form_after_build['#attached']['drupalSettings'] = array_replace_recursive(
      $form_after_build['#attached']['drupalSettings'] ?? [],
      $yoastSeoSettingsBuilder->getSettings()
    );

    return $form_after_build;
  }

  private function formGet($form, $key) {
    return NestedArray::getValue(
      $form,
      explode('.', $key)
    );
  }

}
