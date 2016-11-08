<?php

namespace ACFComposer;

use Exception;

class ResolveConfig {
  public static function forField($config) {
    return self::forEntity($config, ['name', 'label', 'type']);
  }

  public static function forLayout($config) {
    return self::forEntity($config, ['name', 'label']);
  }

  protected static function forEntity($config, $requiredAttributes) {
    if(is_string($config)) {
      $config = apply_filters($config, null);
    }
    $output = self::validateConfig($config, $requiredAttributes);
    $output = self::forNestedEntities($output);
    return $output;
  }

  protected static function forNestedEntities($config) {
    if(array_key_exists('sub_fields', $config)) {
      $config['sub_fields'] = array_map('self::forField', $config['sub_fields']);
    }
    return $config;
  }

  protected static function validateConfig($config, $requiredAttributes = []) {
    array_walk($requiredAttributes, function($key) use ($config){
      if(!array_key_exists($key, $config)) {
        throw new Exception("Field config needs to contain a \'{$key}\' property.");
      }
    });
    if(array_key_exists('key', $config)) {
      throw new Exception('Field config must not contain a \'key\' property.');
    }
    return $config;
  }
}
