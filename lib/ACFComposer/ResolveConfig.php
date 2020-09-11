<?php

namespace ACFComposer;

use Exception;

class ResolveConfig
{
    /**
     * Validates and resolves a configuration for a local field group.
     *
     * @param array $config Configuration array for the local field group.
     *
     * @return array Resolved field group configuration.
     */
    public static function forFieldGroup($config)
    {
        $output = self::validateConfig($config, ['name', 'title', 'fields', 'location']);
        $keySuffix = $output['name'];
        $output['key'] = "group_{$keySuffix}";
        $output['fields'] = array_reduce($config['fields'], function ($carry, $fieldConfig) use ($keySuffix) {
            $fields = self::forField($fieldConfig, [$keySuffix]);
            self::pushSingleOrMultiple($carry, $fields);
            return $carry;
        }, []);
        $output['location'] = array_map('self::mapLocation', $output['location']);
        return $output;
    }

    /**
     * Validates a location the configuration for a field group location.
     *
     * @param array $config Configuration array for a location of a field group.
     *
     * @return array Valid config.
     */
    public static function forLocation($config)
    {
        return self::validateConfig($config, ['param', 'operator', 'value']);
    }

    /**
     * Validates and resolves a field configuration.
     *
     * @param array $config Configuration array for a any kind of field.
     * @param array $parentKeys Previously used keys of all parent fields.
     *
     * @return array Resolved config for a field.
     */
    public static function forField($config, $parentKeys = [])
    {
        return self::forEntity($config, ['name', 'label', 'type'], $parentKeys);
    }

    /**
     * Validates and resolves a layout configuration of a flexible content field.
     *
     * @param array $config Configuration array for the local field group.
     * @param array $parentKeys Previously used keys of all parent fields.
     *
     * @return array Resolved config for a layout of a flexible content field.
     */
    public static function forLayout($config, $parentKeys = [])
    {
        return self::forEntity($config, ['name', 'label'], $parentKeys);
    }


    /**
     * Validates and resolves configuration for a field, subfield, or layout. Applies prefix through filter arguments.
     *
     * @param array $config Configuration array for the nested entity.
     * @param array $requiredAttributes Required attributes.
     * @param array $parentKeys Previously used keys of all parent fields.
     * @param string $prefix Optional prefix for named field based on filter arguments.
     *
     * @return array Resolved config.
     */
    protected static function forEntity($config, $requiredAttributes, $parentKeys = [], $prefix = null)
    {
        if (is_string($config)) {
            $filterName = $config;
            $filterParts = explode('#', $filterName);
            if (isset($filterParts[1])) {
                $prefix = $filterParts[1];
                $config = apply_filters($filterParts[0], null, $prefix);
                if (!self::isAssoc($config)) {
                    $config = array_map(function ($singleConfig) use ($prefix) {
                        $singleConfig['name'] = $prefix . '_' . $singleConfig['name'];
                        return $singleConfig;
                    }, $config);
                } else {
                    $config['name'] = $prefix . '_' . $config['name'];
                }
            } else {
                $config = apply_filters($filterName, null);
            }


            if (is_null($config)) {
                trigger_error("ACFComposer: Filter {$filterName} does not exist!", E_USER_WARNING);
                return [];
            }
        }
        if (!self::isAssoc($config)) {
            return array_map(function ($singleConfig) use ($requiredAttributes, $parentKeys, $prefix) {
                return self::forEntity($singleConfig, $requiredAttributes, $parentKeys, $prefix);
            }, $config);
        }

        $output = self::validateConfig($config, $requiredAttributes);

        $parentKeysIncludingPrefix = isset($prefix) ? array_merge($parentKeys, [$prefix]) : $parentKeys;
        $output = self::forConditionalLogic($output, $parentKeysIncludingPrefix);

        array_push($parentKeys, $output['name']);

        $keySuffix = implode('_', $parentKeys);
        $output['key'] = "field_{$keySuffix}";

        $output = apply_filters('ACFComposer/resolveEntity', $output);
        $output = apply_filters("ACFComposer/resolveEntity?name={$output['name']}", $output);
        $output = apply_filters("ACFComposer/resolveEntity?key={$output['key']}", $output);
        $output = self::forNestedEntities($output, $parentKeys);
        return $output;
    }

    /**
     * Validates and resolves configuration for subfields and layouts.
     *
     * @param array $config Configuration array for the nested entity.
     * @param array $parentKeys Previously used keys of all parent fields.
     *
     * @return array Resolved config.
     */
    protected static function forNestedEntities($config, $parentKeys)
    {
        if (array_key_exists('sub_fields', $config)) {
            $config['sub_fields'] = array_reduce($config['sub_fields'], function ($output, $field) use ($parentKeys) {
                $fields = self::forField($field, $parentKeys);
                if (!self::isAssoc($fields)) {
                    foreach ($fields as $field) {
                        array_push($output, $field);
                    }
                } else {
                    array_push($output, $fields);
                }
                return $output;
            }, []);
        }
        if (array_key_exists('layouts', $config)) {
            $config['layouts'] = array_reduce($config['layouts'], function ($output, $layout) use ($parentKeys) {
                $layouts = self::forLayout($layout, $parentKeys);
                if (!self::isAssoc($layouts)) {
                    foreach ($layouts as $layout) {
                        array_push($output, $layout);
                    }
                } else {
                    array_push($output, $layouts);
                }
                return $output;
            }, []);
        }
        return $config;
    }

    /**
     * Validates a configuration array based on given required attributes.
     *
     * Usually the field key has to be provided for conditional logic to work. Since all keys are generated automatically by this plugin, you can instead provide a 'relative path' to a field by it's name.
     *
     * @param array $config Configuration array.
     * @param array $requiredAttributes Required Attributes.
     *
     * @throws Exception if a required attribute is not present.
     * @throws Exception if the `key` attribute is not present.
     *
     * @return array Given $config.
     */
    protected static function validateConfig($config, $requiredAttributes = [])
    {
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

    /**
     * Maps location configurations to their resolved config arrays.
     *
     * @param array $locationArray All locations for a field group.
     *
     * @return array Resolved locations array.
     */
    protected static function mapLocation($locationArray)
    {
        return array_map('self::forLocation', $locationArray);
    }

    /**
     * Resolves a field's conditional logic attribute.
     *
     * Usually the field key has to be provided for conditional logic to work. Since all keys are generated automatically by this plugin, you can instead provide a 'relative path' to a field by it's name.
     *
     * @param array $config Configuration array for the conditional logic attribute.
     * @param array $parentKeys Previously used keys of all parent fields.
     *
     * @return array Resolved conditional logic attribute.
     */
    protected static function forConditionalLogic($config, $parentKeys)
    {
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

    /**
     * Checks whether or not a given array is associative.
     *
     * @param array $arr Array to check.
     *
     * @return boolean
     */
    protected static function isAssoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Adds a single or multiple elements to an array.
     *
     * @param array &$arr Array to add to.
     * @param array $fields Single or multiple associative arrays to add to $arr.
     *
     * @return boolean
     */
    protected static function pushSingleOrMultiple(array &$carry, array $fields)
    {
        if (!self::isAssoc($fields)) {
            foreach ($fields as $field) {
                self::pushSingleOrMultiple($carry, $field);
            }
        } else {
            array_push($carry, $fields);
        }
        return $carry;
    }
}
