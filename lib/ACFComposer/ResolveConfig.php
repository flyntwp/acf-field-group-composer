<?php

namespace ACFComposer;

use Exception;

class ResolveConfig {
  public static function forFieldGroup($config) {
    $output = self::validateConfig($config, ['name', 'title', 'fields', 'location']);

    $keySuffix = $output['name'];
    $output['key'] = "group_{$keySuffix}";
    $output['fields'] = array_map(function ($field) use ($keySuffix) {
      return self::forField($field, $keySuffix);
    }, $output['fields']);
    $output['location'] = array_map('self::mapLocation', $output['location']);
    return $output;
  }

  public static function forLocation($config) {
    return self::validateConfig($config, ['param', 'operator', 'value']);
  }

  public static function forField($config, $keySuffix = '') {
    return self::forEntity($config, ['name', 'label', 'type'], $keySuffix);
  }

  public static function forLayout($config, $keySuffix = '') {
    return self::forEntity($config, ['name', 'label'], $keySuffix);
  }

  protected static function forEntity($config, $requiredAttributes, $parentKeySuffix = '') {
    if (is_string($config)) {
      $config = apply_filters($config, null);
    }
    $output = self::validateConfig($config, $requiredAttributes);

    $keySuffix = empty($parentKeySuffix)
      ? $output['name']
      : "{$parentKeySuffix}_{$output['name']}";
    $output['key'] = "field_{$keySuffix}";

    $output = apply_filters('ACFComposer/resolveEntity', $output);
    $output = apply_filters("ACFComposer/resolveEntity?name={$output['name']}", $output);
    $output = apply_filters("ACFComposer/resolveEntity?key={$output['key']}", $output);
    $output = self::forNestedEntities($output, $keySuffix);
    return $output;
  }

  protected static function forNestedEntities($config, $parentKeySuffix) {
    if (array_key_exists('sub_fields', $config)) {
      $config['sub_fields'] = array_map(function ($field) use ($parentKeySuffix) {
        return self::forField($field, $parentKeySuffix);
      }, $config['sub_fields']);
    }
    if (array_key_exists('layouts', $config)) {
      $config['layouts'] = array_map(function ($layout) use ($parentKeySuffix) {
        return self::forLayout($layout, $parentKeySuffix);
      }, $config['layouts']);
    }
    return $config;
  }

  protected static function validateConfig($config, $requiredAttributes = []) {
    array_walk($requiredAttributes, function ($key) use ($config) {
      if (!array_key_exists($key, $config)) {
        throw new Exception("Field config needs to contain a \'{$key}\' property.");
      }
    });
    if (array_key_exists('key', $config)) {
      throw new Exception('Field config must not contain a \'key\' property.');
    }
    return $config;
  }

  protected static function mapLocation($locationArray) {
    return array_map('self::forLocation', $locationArray);
  }
}
