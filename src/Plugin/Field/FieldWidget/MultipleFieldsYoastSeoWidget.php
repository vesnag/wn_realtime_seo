<?php

namespace Drupal\wn_realtime_seo\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\Entity\FieldStorageConfig;
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
class MultipleFieldsYoastSeoWidget extends YoastSeoWidget implements ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Instance of YoastSeoManager service.
   */
  protected $yoastSeoManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_field.manager'),
      $container->get('yoast_seo.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityFieldManagerInterface $entity_field_manager, YoastSeoManager $manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $entity_field_manager, $manager);
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
    $settings = $this->getSettings();
    $t = 1;

    $element = [];
    /** @var EntityFormDisplayInterface $form_display */
    $form_display = $form_state->getFormObject()->getEntity();
    $entity_type = $form_display->getTargetEntityTypeId();
    $bundle = $form_display->getTargetBundle();
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);

    if (empty($fields)) {
      return [];
    }

    // TODO DI,
    $text_field_filter = \Drupal::service('wn_realtime_seo.text_field_filter');
    $text_fields = $text_field_filter->filterTextFields($fields);

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

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form['#yoast_settings'] = $this->getSettings();

    // Create the form element.
    $element['yoast_seo'] = array(
      '#type' => 'details',
      '#title' => $this->t('Real-time SEO for drupal'),
      '#open' => TRUE,
      '#attached' => array(
        'library' => array(
          'yoast_seo/yoast_seo_core',
          'yoast_seo/yoast_seo_admin',
        ),
      ),
    );

    $element['yoast_seo']['focus_keyword'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Focus keyword'),
      '#default_value' => isset($items[$delta]->focus_keyword) ? $items[$delta]->focus_keyword : NULL,
      '#description' => $this->t("Pick the main keyword or keyphrase that this post/page is about."),
    );

    $element['yoast_seo']['status'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('Real-time SEO status'),
      '#default_value' => isset($items[$delta]->status) ? $items[$delta]->status : NULL,
      '#description' => $this->t("The SEO status in points."),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['status']        = $value['yoast_seo']['status'];
      $value['focus_keyword'] = $value['yoast_seo']['focus_keyword'];
    }
    return $values;
  }

}
