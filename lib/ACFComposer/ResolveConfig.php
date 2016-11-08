<?php

namespace ACFComposer;

use Exception;

class ResolveConfig {
  public static function forField($config) {
    return self::forGeneric($config, ['name', 'label', 'type']);
  }

  public static function forLayout($config) {
    return self::forGeneric($config, ['name', 'label']);
  }

  protected static function forGeneric($config, $requiredAttributes) {
    if(is_string($config)) {
      $config = apply_filters($config, null);
    }
    return self::validateConfig($config, $requiredAttributes);
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
