<?php

namespace Drupal\wn_realtime_seo\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wn_realtime_seo\TextFieldFilter;
use Drupal\yoast_seo\Plugin\Field\FieldWidget\YoastSeoWidget;
use Drupal\yoast_seo\YoastSeoManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Advanced widget for yoast_seo field with multi fields selection.
 *
 * @FieldWidget(
 *   id = "multiple_fields_yoast_seo_widget",
 *   label = @Translation("Real-time SEO form - Multiple fields"),
 *   field_types = {
 *     "yoast_seo"
 *   }
 * )
 */
final class MultipleFieldsYoastSeoWidget extends YoastSeoWidget implements ContainerFactoryPluginInterface {

  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityFieldManagerInterface $entity_field_manager,
    YoastSeoManager $manager,
    private TextFieldFilter $textFieldFilter
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $entity_field_manager, $manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    /** @var EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = $container->get('entity_field.manager');

    /** @var YoastSeoManager $yoastSeoManager */
    $yoastSeoManager = $container->get('yoast_seo.manager');

    /** @var TextFieldFilter TextFieldFilter */
    $textFieldFilter = $container->get('wn_realtime_seo.text_field_filter');

    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $entityFieldManager,
      $yoastSeoManager,
      $textFieldFilter
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'body' => 'body',
      'summary' => 'summary',
    ];

    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Body: @body', [
      '@body' => $this->getSetting('body'),
    ]);

    $summary[] = $this->t('Summary: @summary', [
      '@summary' => $this->getSetting('summary')
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    /** @var EntityFormDisplayInterface $form_display */
    $form_display = $form_state->getFormObject()->getEntity();
    $entity_type = $form_display->getTargetEntityTypeId();
    $bundle = $form_display->getTargetBundle();

    /** @var array $fields */
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);

    if (empty($fields)) {
      return [];
    }

    $text_fields = $this->textFieldFilter->filterTextFields($fields);

    $element['body'] = [
      '#type' => 'select',
      '#title' => $this->t('Main Text'),
      '#required' => TRUE,
      '#description' => $this->t('Select fields which are used for the analysis for the main text.'),
      '#options' => $text_fields,
      '#default_value' => $this->getSetting('body'),
    ];

    $element['summary'] = [
      '#type' => 'select',
      '#title' => $this->t('Summary'),
      '#required' => TRUE,
      '#description' => $this->t('Select fields which are used for the analysis for the summary.'),
      '#options' => $text_fields,
      '#default_value' => $this->getSetting('summary'),
    ];

    return $element;
  }

}
