<?php

namespace ACFComposer;

use Exception;

class ResolveConfig {
  public static function forField($config) {
    if(is_string($config)) {
      $config = apply_filters($config, null);
    }
    return self::validateConfig($config);
  }

  protected static function validateConfig($config) {
    if(!array_key_exists('name', $config)) {
      throw new Exception('Field config needs to contain a \'name\' property.');
    }
    if(!array_key_exists('label', $config)) {
      throw new Exception('Field config needs to contain a \'label\' property.');
    }
    if(!array_key_exists('type', $config)) {
      throw new Exception('Field config needs to contain a \'type\' property.');
    }
    if(array_key_exists('key', $config)) {
      throw new Exception('Field config must not contain a \'key\' property.');
    }
    return $config;
  }
}
