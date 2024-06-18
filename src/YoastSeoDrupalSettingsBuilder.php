<?php

namespace Drupal\wn_realtime_seo;

final class YoastSeoDrupalSettingsBuilder {

  /**
   * @var array<string,array<string,mixed>>
   */
  private array $settings = [];

  public function setFields(array $fields): void {
    $this->settings['yoast_seo']['fields'] = array_merge(
      $this->settings['yoast_seo']['fields'] ?? [],
      $fields
    );
  }

  public function setTokens(array $tokens): void {
    $this->settings['yoast_seo']['tokens'] = array_merge(
      $this->settings['yoast_seo']['tokens'] ?? [],
      $tokens
    );
  }

  public function setDefaultText(array $default_text): void {
    $this->settings['yoast_seo']['default_text'] = $default_text;
  }

  public function setPlaceholders(array $placeholders): void {
    $this->settings['yoast_seo']['placeholder_text'] = $placeholders;
  }

  public function setSeoTitleOverwritten(bool $seo_title_overwritten): void {
    $this->settings['yoast_seo']['seo_title_overwritten'] = $seo_title_overwritten;
  }

  public function setTextFormat(string $text_format): void {
    $this->settings['yoast_seo']['text_format'] = $text_format;
  }

  public function setFormId(string $form_id): void {
    $this->settings['yoast_seo']['form_id'] = $form_id;
  }

  public function getSettings(): array {
    return $this->settings;
  }

}
