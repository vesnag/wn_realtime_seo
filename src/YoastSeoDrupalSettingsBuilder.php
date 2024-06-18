<?php

namespace Drupal\wn_realtime_seo;

final class YoastSeoDrupalSettingsBuilder {

  /**
   * @var array<string,array<string,mixed>>
   */
  private array $settings = [];

  public function setFields($fields) {
    $this->settings['yoast_seo']['fields'] = array_merge(
      $this->settings['yoast_seo']['fields'] ?? [],
      $fields
    );
  }

  public function setTokens($tokens) {
    $this->settings['yoast_seo']['tokens'] = array_merge(
      $this->settings['yoast_seo']['tokens'] ?? [],
      $tokens
    );
  }

  public function setDefaultText($defaultText) {
    $this->settings['yoast_seo']['default_text'] = $defaultText;
  }

  public function setPlaceholders($placeholders) {
    $this->settings['yoast_seo']['placeholder_text'] = $placeholders;
  }

  public function setSeoTitleOverwritten($seoTitleOverwritten) {
    $this->settings['yoast_seo']['seo_title_overwritten'] = $seoTitleOverwritten;
  }

  public function setTextFormat($textFormat) {
    $this->settings['yoast_seo']['text_format'] = $textFormat;
  }

  public function setFormId($formId) {
    $this->settings['yoast_seo']['form_id'] = $formId;
  }

  public function getSettings() {
    return $this->settings;
  }

}
