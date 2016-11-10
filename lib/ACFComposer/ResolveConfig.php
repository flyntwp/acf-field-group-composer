<?php

namespace ACFComposer;

use Exception;

class ResolveConfig {
  public static function forFieldGroup($config) {
    $output = self::validateConfig($config, ['name', 'title', 'fields', 'location']);

    $keySuffix = $output['name'];
    $output['key'] = "group_{$keySuffix}";
    $output['fields'] = array_map(function ($field) use ($keySuffix) {
      return self::forField($field, [$keySuffix]);
    }, $output['fields']);
    $output['location'] = array_map('self::mapLocation', $output['location']);
    return $output;
  }

  public static function forLocation($config) {
    return self::validateConfig($config, ['param', 'operator', 'value']);
  }

  public static function forField($config, $parentKeys = []) {
    return self::forEntity($config, ['name', 'label', 'type'], $parentKeys);
  }

  public static function forLayout($config, $parentKeys = []) {
    return self::forEntity($config, ['name', 'label'], $parentKeys);
  }

  protected static function forEntity($config, $requiredAttributes, $parentKeys = []) {
    if (is_string($config)) {
      $config = apply_filters($config, null);
    }
    $output = self::validateConfig($config, $requiredAttributes);

    $output = self::forConditionalLogic($output, $parentKeys);

    array_push($parentKeys, $output['name']);

    $keySuffix = implode('_', $parentKeys);
    $output['key'] = "field_{$keySuffix}";

    $output = apply_filters('ACFComposer/resolveEntity', $output);
    $output = apply_filters("ACFComposer/resolveEntity?name={$output['name']}", $output);
    $output = apply_filters("ACFComposer/resolveEntity?key={$output['key']}", $output);
    $output = self::forNestedEntities($output, $parentKeys);
    return $output;
  }

  protected static function forNestedEntities($config, $parentKeys) {
    if (array_key_exists('sub_fields', $config)) {
      $config['sub_fields'] = array_map(function ($field) use ($parentKeys) {
        return self::forField($field, $parentKeys);
      }, $config['sub_fields']);
    }
    if (array_key_exists('layouts', $config)) {
      $config['layouts'] = array_map(function ($layout) use ($parentKeys) {
        return self::forLayout($layout, $parentKeys);
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

  protected static function forConditionalLogic($config, $parentKeys) {
    if (array_key_exists('conditional_logic', $config)) {
      $config['conditional_logic'] = array_map(function ($conditionGroup) use ($parentKeys) {
        return array_map(function ($condition) use ($parentKeys) {
          if (array_key_exists('fieldPath', $condition)) {
            $conditionalField = $condition['fieldPath'];
            while (substr($conditionalField, 0, 3) === '../') {
              $conditionalField = substr($conditionalField, 3);
              array_pop($parentKeys);
            }
            array_push($parentKeys, $conditionalField);
            $keySuffix = implode('_', $parentKeys);
            $condition['field'] = "field_{$keySuffix}";
            unset($condition['fieldPath']);
          }
          return $condition;
        }, $conditionGroup);
      }, $config['conditional_logic']);
    }
    return $config;
  }
}
