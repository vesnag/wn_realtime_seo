<?php

namespace Drupal\wn_realtime_seo;

final class YoastSeoDrupalSettingsBuilder {

  /**
   * @var array<string,array<string,mixed>>
   */
  private array $settings = [];

  public function setFields(array $fields) {
    $this->settings['yoast_seo']['fields'] = array_merge(
      $this->settings['yoast_seo']['fields'] ?? [],
      $fields
    );
  }

  public function setTokens(array $tokens) {
    $this->settings['yoast_seo']['tokens'] = array_merge(
      $this->settings['yoast_seo']['tokens'] ?? [],
      $tokens
    );
  }

  public function setDefaultText(string $default_text) {
    $this->settings['yoast_seo']['default_text'] = $default_text;
  }

  public function setPlaceholders(string $placeholders) {
    $this->settings['yoast_seo']['placeholder_text'] = $placeholders;
  }

  public function setSeoTitleOverwritten(string $seo_title_overwritten) {
    $this->settings['yoast_seo']['seo_title_overwritten'] = $seo_title_overwritten;
  }

  public function setTextFormat(string $text_format) {
    $this->settings['yoast_seo']['text_format'] = $text_format;
  }

  public function setFormId(string $form_id) {
    $this->settings['yoast_seo']['form_id'] = $form_id;
  }

  public function getSettings() {
    return $this->settings;
  }

}
