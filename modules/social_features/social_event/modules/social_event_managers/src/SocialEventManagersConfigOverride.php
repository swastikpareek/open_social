<?php

namespace Drupal\social_event_managers;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class SocialEventManagersConfigOverride.
 *
 * Example configuration override.
 *
 * @package Drupal\social_event_managers
 */
class SocialEventManagersConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * Load overrides.
   */
  public function loadOverrides($names) {
    $overrides = [];
    $config_name = 'core.entity_form_display.node.event.default';
    if (in_array($config_name, $names)) {
      $config = \Drupal::service('config.factory')->getEditable($config_name);
      // Add a field group.
      if ($group_attachment = $config->get('third_party_settings.field_group.group_attachments')) {
        $group_attachment['children'][] = 'field_event_managers';
        $overrides[$config_name]['third_party_settings']['field_group']['group_attachments'] = $group_attachment;
      }

      // Add the field to the content.
      $content = $config->get('content');
      $content['field_event_managers'] = [];
      $content['field_event_managers']['settings'] = [];
      $content['field_event_managers']['settings']['match_operator'] = 'CONTAINS';
      $content['field_event_managers']['settings']['placeholder'] = '';
      $content['field_event_managers']['settings']['size'] = '60';
      $content['field_event_managers']['type'] = 'entity_reference_autocomplete';
      $content['field_event_managers']['weight'] = 13;
      if (!isset($content['field_event_managers']['third_party_settings'])) {
        $content['field_event_managers']['third_party_settings'] = [];
      }

      $overrides[$config_name]['content'] = $content;

    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SocialEventManagersConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
